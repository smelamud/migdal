package ua.org.migdal.controller;

import javax.inject.Inject;
import javax.validation.Valid;

import org.springframework.stereotype.Controller;
import org.springframework.transaction.PlatformTransactionManager;
import org.springframework.ui.Model;
import org.springframework.util.StringUtils;
import org.springframework.validation.Errors;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.ModelAttribute;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.ResponseBody;
import org.springframework.web.servlet.mvc.support.RedirectAttributes;
import org.springframework.web.util.UriComponentsBuilder;

import ua.org.migdal.Config;
import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.data.User;
import ua.org.migdal.data.UserRight;
import ua.org.migdal.data.api.UserInfo;
import ua.org.migdal.form.UserForm;
import ua.org.migdal.helper.util.Constant;
import ua.org.migdal.mail.MailController;
import ua.org.migdal.manager.UserManager;
import ua.org.migdal.location.LocationInfo;
import ua.org.migdal.session.RequestContext;
import ua.org.migdal.util.Captcha;
import ua.org.migdal.util.Utils;

@Controller
public class UserController {

    private static final Constant[] SORTS = new Constant[] {
            new Constant<>("по нику", "login"),
            new Constant<>("по имени", "name"),
            new Constant<>("по фамилии", "surname")
    };

    @Inject
    private Config config;

    @Inject
    private PlatformTransactionManager txManager;

    @Inject
    private RequestContext requestContext;

    @Inject
    private UserManager userManager;

    @Inject
    private Captcha captcha;

    @Inject
    private MailController mailController;

    @Inject
    private IndexController indexController;

    @Inject
    private AdminController adminController;

    @GetMapping("/users")
    public String users() {
        return requestContext.isUserAdminUsers() ? "redirect:/admin/users" : "redirect:/";
    }

    @GetMapping("/users/{folder}")
    public String userInfo(@PathVariable String folder, Model model) throws PageNotFoundException {
        User user = userManager.beg(userManager.idOrLogin(folder));
        if (user == null) {
            throw new PageNotFoundException();
        }

        userInfoLocationInfo(folder, user.getLogin(), model);

        model.addAttribute("user", user);
        model.addAttribute("admin", false);
        return "user-info";
    }

    public LocationInfo userInfoLocationInfo(String folder, String login, Model model) {
        return new LocationInfo(model)
                .withUri("/users/" + folder)
                .withParent(indexController.indexLocationInfo(null))
                .withPageTitle("О пользователе " + login);
    }

    @GetMapping("/api/user/{id}")
    @ResponseBody
    public UserInfo loginExists(@PathVariable long id) {
        User user = userManager.beg(id);
        return new UserInfo(
                user.getLogin(),
                user.getFullName().toString(),
                user.getInfoOrRankHtml().toString(),
                user.isWoman(),
                user.getLastOnline().getTime() / 1000);
    }

    @GetMapping("/users/{folder}/edit")
    public String userEdit(@PathVariable String folder, Model model) throws PageNotFoundException {
        User user = userManager.beg(userManager.idOrLogin(folder));
        if (user == null) {
            throw new PageNotFoundException();
        }

        userEditLocationInfo(folder, user.getLogin(), model);

        model.addAttribute("user", user);
        model.asMap().computeIfAbsent("userForm", key -> new UserForm(user));
        return "user-edit";
    }

    public LocationInfo userEditLocationInfo(String folder, String login, Model model) {
        return new LocationInfo(model)
                .withUri("/users/" + folder + "/edit")
                .withParent(userInfoLocationInfo(folder, login, null))
                .withPageTitle("Изменение информации о пользователе");
    }

    @PostMapping("/actions/user/modify")
    public String actionUserModify(
            @ModelAttribute @Valid UserForm userForm,
            Errors errors,
            RedirectAttributes redirectAttributes) {
        User user = userForm.getId() > 0 ? userManager.get(userForm.getId()) : new User();
        String userFolder = user.getFolder(); // The folder before login modification
        new ControllerAction(UserController.class, "actionUserModify", errors)
                .transactional(txManager)
                .constraint("users_login_key", "newLogin.used")
                .execute(() -> {
                    if (userForm.getId() <= 0) {
                        if (config.isDisableRegister() && !requestContext.isUserAdminUsers()) {
                            return "disabled";
                        }
                    } else {
                        if (user.getId() <= 0) {
                            return "noUser";
                        }
                    }

                    if (!user.isEditable(requestContext)) {
                        return "notEditable";
                    }
                    if (Utils.isNumber(userForm.getNewLogin())) {
                        return "newLogin.number";
                    }
                    String errorCode = validateRights(userForm);
                    if (errorCode != null) {
                        return errorCode;
                    }
                    if (!requestContext.isUserAdminUsers() && !captcha.valid(userForm.getCaptchaResponse())) {
                        return "wrongCaptcha";
                    }

                    userForm.toUser(user, requestContext.isUserAdminUsers(), config);
                    userManager.save(user);
                    if (userForm.getId() <= 0) {
                        mailController.register(user);
                        mailController.registering(user);
                    }
                    return null;
                });

        if (userForm.getId() <= 0) {
            if (!errors.hasErrors()) {
                if (requestContext.isUserAdminUsers()) {
                    return "redirect:" + requestContext.getOrigin();
                } else {
                    redirectAttributes.addFlashAttribute("id", user.getId());
                    return UriComponentsBuilder.fromUriString("redirect:/register/confirm")
                            .queryParam("back", requestContext.getOrigin())
                            .toUriString();
                }
            } else {
                redirectAttributes.addFlashAttribute("errors", errors);
                redirectAttributes.addFlashAttribute("userForm", userForm);
                return UriComponentsBuilder.fromUriString("redirect:/register")
                        .queryParam("back", requestContext.getOrigin())
                        .toUriString();
            }
        } else {
            if (!errors.hasErrors()) {
                return "redirect:/users/" + user.getFolder();
            } else {
                redirectAttributes.addFlashAttribute("errors", errors);
                redirectAttributes.addFlashAttribute("userForm", userForm);
                return "redirect:/users/" + userFolder + "/edit";
            }
        }
    }

    private String validateRights(UserForm userForm) {
        if (userForm.getRights() == null) {
            return null;
        }

        for (long right : userForm.getRights()) {
            UserRight userRight = UserRight.findByValue(right);
            if (userRight == null) {
                return "noRight";
            }
            if (userRight.isAdmin() && !requestContext.isUserAdminUsers()) {
                return "adminRight";
            }
        }
        return null;
    }

    @GetMapping("/admin/users")
    public String adminUsers(
            @RequestParam(defaultValue="") String prefix,
            @RequestParam(defaultValue="login") String sort,
            @RequestParam(defaultValue="0") Integer offset,
            Model model) throws PageNotFoundException {

        adminUsersLocationInfo(model);

        if (!StringUtils.isEmpty(sort) && !Constant.hasValue(SORTS, sort)) {
            throw new PageNotFoundException();
        }

        model.addAttribute("prefix", prefix);
        model.addAttribute("sort", sort);
        model.addAttribute("sorts", SORTS);
        if (requestContext.isUserModerator()) {
            model.addAttribute("totalUsers", userManager.count());
            model.addAttribute("totalNotConfirmedUsers", userManager.countNotConfirmed());
            model.addAttribute("users", userManager.begAll(prefix, sort, offset, 20));
        }
        return "admin-users";
    }

    public LocationInfo adminUsersLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/admin/users")
                .withTopics("topics-admin")
                .withTopicsIndex("admin-users")
                .withParent(adminController.adminLocationInfo(null))
                .withPageTitle("Пользователи");
    }

}