package ua.org.migdal.controller;

import javax.validation.Valid;

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
import ua.org.migdal.form.SuForm;
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

    @Autowired
    private IndexController indexController;

    @GetMapping("/signin")
    public String signin(Model model) {
        signinLocationInfo(model);

        model.addAttribute("novice", 0);
        if (!requestContext.isLogged()) {
            model.asMap().putIfAbsent("loginForm", new LoginForm());
        } else {
            model.asMap().putIfAbsent("suForm", new SuForm());
        }
        return "signin";
    }

    public LocationInfo signinLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/signin")
                .withParent(indexController.indexLocationInfo(null))
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
                    User user = usersManager.getByLogin(loginForm.getLogin());
                    String md5Password = Utils.md5(loginForm.getPassword());
                    if (user == null || !md5Password.equalsIgnoreCase(user.getPassword())) {
                        return "incorrect";
                    }
                    if (user.isNoLogin()) {
                        return "banned";
                    }
                    session.setUserId(user.getId());
                    session.setRealUserId(user.getId());
                    session.setDuration(loginForm.isMyComputer()
                            ? config.getSessionTimeoutLong()
                            : config.getSessionTimeoutShort());
                    return null;
                });

        if (!errors.hasErrors()) {
            if (requestContext.isHasBack()) {
                return "redirect:" + requestContext.getBack();
            } else {
                return "redirect:/register/ok";
            }
        } else {
            redirectAttributes.addFlashAttribute("errors", errors);
            redirectAttributes.addFlashAttribute("loginForm", loginForm);
            return UriComponentsBuilder.fromUriString("redirect:/signin")
                    .queryParam("back", requestContext.getBack())
                    .toUriString();
        }
    }

    @PostMapping("/actions/logout")
    public String actionLogout() {
        if (session.getUserId() > 0 && session.getUserId() != session.getRealUserId()) {
            session.setUserId(session.getRealUserId());
        } else {
            session.setUserId(0);
            session.setRealUserId(usersManager.getGuestId());
        }
        return "redirect:" + requestContext.getBack();
    }

    @PostMapping("/actions/su")
    public String actionSu(
            @ModelAttribute @Valid SuForm suForm,
            Errors errors,
            RedirectAttributes redirectAttributes) {
        new ControllerAction(LoginController.class, "actionSu", errors)
                .execute(() -> {
                    if (!requestContext.isUserAdminUsers()) {
                        return "notAdmin";
                    }
                    long userId = usersManager.getIdByLogin(suForm.getLogin());
                    if (userId <= 0) {
                        return "noUser";
                    }
                    session.setUserId(userId);
                    return null;
                });

        if (!errors.hasErrors()) {
            return "redirect:" + requestContext.getBack();
        } else {
            redirectAttributes.addFlashAttribute("errors", errors);
            redirectAttributes.addFlashAttribute("suForm", suForm);
            return UriComponentsBuilder.fromUriString("redirect:/signin")
                    .queryParam("back", requestContext.getBack())
                    .toUriString();
        }
    }

}