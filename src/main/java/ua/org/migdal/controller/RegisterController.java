package ua.org.migdal.controller;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import javax.inject.Inject;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.ResponseBody;

import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.data.User;
import ua.org.migdal.data.api.LoginExistence;
import ua.org.migdal.form.LoginForm;
import ua.org.migdal.form.UserForm;
import ua.org.migdal.mail.MailController;
import ua.org.migdal.mail.exception.MailServiceException;
import ua.org.migdal.manager.UserManager;
import ua.org.migdal.location.LocationInfo;
import ua.org.migdal.session.RequestContext;

@Controller
public class RegisterController {

    private static Logger log = LoggerFactory.getLogger(RegisterController.class);

    @Inject
    private RequestContext requestContext;

    @Inject
    private UserManager userManager;

    @Inject
    private MailController mailController;

    @Inject
    private IndexController indexController;

    @GetMapping("/register")
    public String register(Model model) {
        registerLocationInfo(model);

        model.addAttribute("captchaOnPage", !requestContext.isUserAdminUsers());
        model.asMap().computeIfAbsent("userForm", key -> new UserForm());
        return "register";
    }

    public LocationInfo registerLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/register")
                .withParent(indexController.indexLocationInfo(null))
                .withPageTitle("Регистрация пользователя");
    }

    @GetMapping("/api/user/login/exists")
    @ResponseBody
    public LoginExistence loginExists(@RequestParam String login) {
        return new LoginExistence(login, userManager.loginExists(login));
    }

    @GetMapping("/register/confirm")
    public String registerConfirm(Model model) throws PageNotFoundException {
        registerConfirmLocationInfo(model);

        Object id = model.asMap().get("id");
        if (id == null || !(id instanceof Long)) {
            throw new PageNotFoundException();
        }
        User user = userManager.get((long) id);
        if (user == null) {
            throw new PageNotFoundException();
        }
        model.addAttribute("user", user);
        return "register-confirm";
    }

    public LocationInfo registerConfirmLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/register/confirm")
                .withParent(indexController.indexLocationInfo(null))
                .withPageTitle("Подтверждение регистрации");
    }

    @GetMapping("/actions/user/confirm") // TODO deprecate this
    @PostMapping("/actions/user/confirm")
    public String actionConfirm(@RequestParam(required = false) Long id, @RequestParam(required = false) String code) {
        User user = null;
        if (requestContext.isUserAdminUsers() && id != null && id != 0) {
            user = userManager.get(id);
        } else {
            user = userManager.begByConfirmCode(code);
        }
        if (user == null) {
            return "redirect:/register/error";
        }
        if (user.isConfirmed()) {
            return "redirect:/register/already-confirmed";
        }
        userManager.confirm(user);
        try {
            mailController.confirmed(user);
        } catch (MailServiceException e) {
            log.error("Mail error while confirming user {} ({}): {}", user.getId(), user.getLogin(), e.getMessage());
            log.error("Exception: ", e);
        }
        return "redirect:/register/signin";
    }

    @GetMapping("/register/signin")
    public String signin(Model model) {
        signinLocationInfo(model);

        model.addAttribute("novice", 1);
        model.asMap().computeIfAbsent("loginForm", key -> new LoginForm());
        return "signin";
    }

    public LocationInfo signinLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/register/signin")
                .withParent(indexController.indexLocationInfo(null))
                .withMenuNoLogin(true)
                .withPageTitle("Вход на сайт");
    }

    @GetMapping("/register/already-confirmed")
    public String alreadyConfirmed(Model model) {
        alreadyConfirmedLocationInfo(model);

        model.addAttribute("novice", 2);
        model.asMap().computeIfAbsent("loginForm", key -> new LoginForm());
        return "signin";
    }

    public LocationInfo alreadyConfirmedLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/register/already-confirmed")
                .withParent(indexController.indexLocationInfo(null))
                .withMenuNoLogin(true)
                .withPageTitle("Вход на сайт");
    }

    @GetMapping("/register/ok")
    public String registerOk(Model model) {
        registerOkLocationInfo(model);

        return "register-ok";
    }

    public LocationInfo registerOkLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/register/ok")
                .withParent(indexController.indexLocationInfo(null))
                .withPageTitle("Вы зашли на сайт");
    }

    @GetMapping("/register/error")
    public String registerError(Model model) {
        registerErrorLocationInfo(model);

        return "register-error";
    }

    public LocationInfo registerErrorLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/register/error")
                .withParent(indexController.indexLocationInfo(null))
                .withPageTitle("Ошибка при подтверждении регистрации");
    }

}