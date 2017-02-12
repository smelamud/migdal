package ua.org.migdal.controller;

import java.util.Map;

import javax.validation.Valid;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.validation.Errors;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.ModelAttribute;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.servlet.mvc.support.RedirectAttributes;
import org.springframework.web.util.UriComponentsBuilder;

import ua.org.migdal.session.Session;
import ua.org.migdal.data.User;
import ua.org.migdal.data.UserRepository;
import ua.org.migdal.form.LoginForm;
import ua.org.migdal.form.LogoutForm;

@Controller
public class LoginController {

    @Autowired
    private Session session;

    @Autowired
    private UserRepository userRepository;

    @GetMapping("/signin")
    public String signin(@RequestParam(required = false) String back, Map<String, Object> model) {
        @SuppressWarnings("unchecked")
        Map<String, Object> menuProps = (Map<String, Object>) model.get("menu");
        menuProps.put("signIn", false);

        /*if (!session.isLoggedIn()) {
            model.putIfAbsent("loginForm", new LoginForm(back, "/"));
            return "signin";
        } else {
            model.putIfAbsent("logoutForm", new LogoutForm(back, "/"));*/
            return "signout";
//        }
    }

    @PostMapping("/signin")
    public String signin(
            @ModelAttribute @Valid LoginForm loginForm,
            Errors errors,
            RedirectAttributes redirectAttributes) {
        if (!errors.hasErrors()) {
            User user = userRepository.findByEmail(loginForm.getEmail());
            if (user != null && loginForm.getPassword().equals(user.getPassword())) {
/*
                if (user.getRole() != null) {
                    session.setUserId(user.getId());
                    session.setDisplayName(user.getDisplayNameAdministrative());
                } else {
                    errors.reject("banned");
                }
*/
            } else {
                errors.reject("incorrect");
            }
        }

        if (!errors.hasErrors()) {
            return "redirect:" + loginForm.getBackUrlSafe();
        } else {
            redirectAttributes.addFlashAttribute("errors", errors);
            redirectAttributes.addFlashAttribute("loginForm", loginForm);
            return UriComponentsBuilder.fromUriString("redirect:/signin")
                    .queryParam("back", loginForm.getBackUrlSafe())
                    .toUriString();
        }
    }

    @PostMapping("/signout")
    public String signout(@ModelAttribute LogoutForm logoutForm) {
        session.setUserId(0);

        return "redirect:" + logoutForm.getBackUrlSafe();
    }

}
