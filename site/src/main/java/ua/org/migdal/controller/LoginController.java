package ua.org.migdal.controller;

import java.nio.charset.StandardCharsets;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;

import javax.validation.Valid;
import javax.xml.bind.DatatypeConverter;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.validation.Errors;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.ModelAttribute;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.servlet.mvc.support.RedirectAttributes;
import org.springframework.web.util.UriComponentsBuilder;

import ua.org.migdal.Config;
import ua.org.migdal.data.User;
import ua.org.migdal.form.LoginForm;
import ua.org.migdal.manager.UsersManager;
import ua.org.migdal.session.LocationInfo;
import ua.org.migdal.session.RequestContext;
import ua.org.migdal.session.Session;
import ua.org.migdal.util.Utils;

@Controller
public class LoginController {

    @Autowired
    private Config config;

    @Autowired
    private RequestContext requestContext;

    @Autowired
    private Session session;

    @Autowired
    private UsersManager usersManager;

/*
    @GetMapping("/signin")
    public String signin(@RequestParam(required = false) String back, Map<String, Object> model) {
        @SuppressWarnings("unchecked")
        Map<String, Object> menuProps = (Map<String, Object>) model.get("menu");
        menuProps.put("signIn", false);

        if (!session.isLoggedIn()) {
            model.putIfAbsent("loginForm", new LoginForm(back, "/"));
            return "signin";
        } else {
            model.putIfAbsent("logoutForm", new LogoutForm(back, "/"));
            return "signout";
        }
    }
*/

    @GetMapping("/signin")
    public String signin(@RequestParam(required = false) Integer novice, Model model) {
        signinLocationInfo(model);

        model.addAttribute("novice", Integer.toString(novice != null ? novice : 0));
        if (!requestContext.isLogged()) {
            model.asMap().putIfAbsent("loginForm", new LoginForm());
        }
        return "signin";
    }

    public static LocationInfo signinLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/signin")
                .withParent(IndexController.indexLocationInfo(null))
                .withMenuNoLogin(true)
                .withPageTitle("Вход на сайт");
    }

    @PostMapping("/actions/login")
    public String actionLogin(
            @ModelAttribute @Valid LoginForm loginForm,
            Errors errors,
            RedirectAttributes redirectAttributes) {
        new ControllerAction(LoginController.class, "actionLogin", errors)
                .execute(() -> {
                    User user = usersManager.findByLogin(loginForm.getLogin());
                    String md5Password = Utils.md5(loginForm.getPassword());
                    if (user == null || !md5Password.equalsIgnoreCase(user.getPassword())) {
                        return "incorrect";
                    }
                    if (user.isNoLogin()) {
                        return "banned";
                    }
                    session.setUserId(user.getId());
                    session.setDuration(loginForm.isMyComputer()
                            ? config.getSessionTimeoutLong()
                            : config.getSessionTimeoutShort());
                    return null;
                });

        if (!errors.hasErrors()) {
            return "redirect:" + requestContext.getBack();
        } else {
            redirectAttributes.addFlashAttribute("errors", errors);
            redirectAttributes.addFlashAttribute("loginForm", loginForm);
            return UriComponentsBuilder.fromUriString("redirect:/signin")
                    .queryParam("back", requestContext.getBack())
                    .toUriString();
        }
    }
/*
    @PostMapping("/signout")
    public String signout(@ModelAttribute LogoutForm logoutForm) {
        session.setUserId(0);

        return "redirect:" + logoutForm.getBackUrlSafe();
    }
*/
}
