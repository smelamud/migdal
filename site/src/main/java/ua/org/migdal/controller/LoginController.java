package ua.org.migdal.controller;

import javax.validation.Valid;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.transaction.PlatformTransactionManager;
import org.springframework.ui.Model;
import org.springframework.validation.Errors;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.ModelAttribute;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.servlet.mvc.support.RedirectAttributes;
import org.springframework.web.util.UriComponentsBuilder;

import ua.org.migdal.Config;
import ua.org.migdal.data.User;
import ua.org.migdal.form.LoginForm;
import ua.org.migdal.form.RecallPasswordForm;
import ua.org.migdal.form.SuForm;
import ua.org.migdal.mail.MailController;
import ua.org.migdal.manager.UserManager;
import ua.org.migdal.session.LocationInfo;
import ua.org.migdal.session.RequestContext;
import ua.org.migdal.session.Session;
import ua.org.migdal.util.Password;
import ua.org.migdal.util.PasswordGenerator;

@Controller
public class LoginController {

    @Autowired
    private Config config;

    @Autowired
    private PlatformTransactionManager txManager;

    @Autowired
    private RequestContext requestContext;

    @Autowired
    private Session session;

    @Autowired
    private UserManager userManager;

    @Autowired
    private MailController mailController;

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
                    User user = userManager.getByLogin(loginForm.getLogin());
                    if (!Password.validate(user, loginForm.getPassword())) {
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
            return "redirect:" + requestContext.getBack();
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
            session.setRealUserId(userManager.getGuestId());
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
                    long userId = userManager.getIdByLogin(suForm.getLogin());
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

    @GetMapping("/recall-password")
    public String recall(Model model) {
        recallLocationInfo(model);

        model.asMap().putIfAbsent("recallPasswordForm", new RecallPasswordForm());
        return "recall-password";
    }

    public LocationInfo recallLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/recall-password")
                .withParent(indexController.indexLocationInfo(null))
                .withPageTitle("Восстановление пароля");
    }

    @PostMapping("/actions/recall-password")
    public String actionRecall(
            @ModelAttribute @Valid RecallPasswordForm recallPasswordForm,
            Errors errors,
            RedirectAttributes redirectAttributes) {
        new ControllerAction(LoginController.class, "actionRecall", errors)
                .transactional(txManager)
                .execute(() -> {
                    User user = userManager.getByLogin(recallPasswordForm.getLogin());
                    if (user == null) {
                        return "login.noUser";
                    }
                    if (!user.isConfirmed()) {
                        return "notConfirmed";
                    }
                    String password = PasswordGenerator.generatePassword();
                    Password.assign(user, password);
                    userManager.save(user);
                    redirectAttributes.addFlashAttribute("user", user);
                    mailController.recallPassword(user, password);
                    mailController.recallingPassword(user);
                    return null;
                });

        if (!errors.hasErrors()) {
            return UriComponentsBuilder.fromUriString("redirect:/recall-password/ok")
                    .queryParam("back", requestContext.getBack())
                    .toUriString();
        } else {
            redirectAttributes.addFlashAttribute("errors", errors);
            redirectAttributes.addFlashAttribute("recallPasswordForm", recallPasswordForm);
            return UriComponentsBuilder.fromUriString("redirect:/recall-password")
                    .queryParam("back", requestContext.getBack())
                    .toUriString();
        }
    }

    @GetMapping("/recall-password/ok")
    public String recallOk(Model model) {
        recallOkLocationInfo(model);

        return "recall-password-ok";
    }

    public LocationInfo recallOkLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/recall-password/ok")
                .withParent(indexController.indexLocationInfo(null))
                .withPageTitle("Новый пароль отправлен");
    }

}