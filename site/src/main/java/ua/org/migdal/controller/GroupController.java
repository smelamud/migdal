package ua.org.migdal.controller;

import javax.inject.Inject;
import javax.validation.Valid;

import org.springframework.stereotype.Controller;
import org.springframework.transaction.PlatformTransactionManager;
import org.springframework.ui.Model;
import org.springframework.validation.Errors;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.ModelAttribute;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.servlet.mvc.support.RedirectAttributes;

import ua.org.migdal.data.User;
import ua.org.migdal.form.GroupAddForm;
import ua.org.migdal.form.GroupDeleteForm;
import ua.org.migdal.manager.GroupManager;
import ua.org.migdal.manager.UserManager;
import ua.org.migdal.session.LocationInfo;
import ua.org.migdal.session.RequestContext;

@Controller
public class GroupController {

    @Inject
    private PlatformTransactionManager txManager;

    @Inject
    private RequestContext requestContext;

    @Inject
    private GroupManager groupManager;

    @Inject
    private UserManager userManager;

    @Inject
    private AdminController adminController;

    @GetMapping("/admin/groups")
    public String adminGroups(Model model) {
        adminGroupsLocationInfo(model);

        model.addAttribute("groups", groupManager.getAll());
        return "admin-groups";
    }

    public LocationInfo adminGroupsLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/admin/groups")
                .withTopics("topics-admin")
                .withTopicsIndex("admin-groups")
                .withParent(adminController.adminLocationInfo(null))
                .withPageTitle("Группы");
    }

    @GetMapping("/admin/groups/add")
    public String adminGroupsAdd(@RequestParam(name="group_name", required=false) String groupName,
                                 @RequestParam(name="user_name", required=false) String userName,
                                 Model model) {
        adminGroupsAddLocationInfo(model);

        model.asMap().computeIfAbsent("groupAddForm", key -> new GroupAddForm(groupName, userName));
        return "admin-groups-add";
    }

    public LocationInfo adminGroupsAddLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/admin/groups/add")
                .withParent(adminGroupsLocationInfo(null))
                .withPageTitle("Добавление пользователя в группу");
    }

    @PostMapping("/actions/group/add")
    public String actionGroupAdd(
            @ModelAttribute @Valid GroupAddForm groupAddForm,
            Errors errors,
            RedirectAttributes redirectAttributes) {
        new ControllerAction(GroupController.class, "actionGroupAdd", errors)
                .transactional(txManager)
                .execute(() -> {
                    if (!requestContext.isUserAdminUsers()) {
                        return "notAdmin";
                    }
                    User group = userManager.getByLogin(groupAddForm.getGroupName());
                    if (group == null) {
                        return "groupName.noGroup";
                    }
                    User user = userManager.getByLogin(groupAddForm.getUserName());
                    if (user == null) {
                        return "userName.noUser";
                    }
                    group.getUsers().add(user);
                    user.getGroups().add(group);
                    userManager.save(group);
                    userManager.save(user);
                    return null;
                });
        if (!errors.hasErrors()) {
            return "redirect:/admin/groups/";
        } else {
            redirectAttributes.addFlashAttribute("errors", errors);
            redirectAttributes.addFlashAttribute("groupAddForm", groupAddForm);
            return "redirect:" + requestContext.getBack();
        }
    }

    @GetMapping("/actions/group/delete") // TODO deprecate this
    @PostMapping("/actions/group/delete")
    public String actionGroupDelete(
            @ModelAttribute @Valid GroupDeleteForm groupDeleteForm,
            Errors errors,
            RedirectAttributes redirectAttributes) {
        new ControllerAction(GroupController.class, "actionGroupDelete", errors)
                .transactional(txManager)
                .execute(() -> {
                    if (!requestContext.isUserAdminUsers()) {
                        return "notAdmin";
                    }
                    User group = userManager.get(groupDeleteForm.getGroupId());
                    if (group == null) {
                        return "noGroup";
                    }
                    User user = userManager.get(groupDeleteForm.getUserId());
                    if (user == null) {
                        return "noUser";
                    }
                    if (!group.getUsers().contains(user)) {
                        return "notMember";
                    }
                    group.getUsers().remove(user);
                    user.getGroups().remove(group);
                    userManager.save(group);
                    userManager.save(user);
                    return null;
                });
        if (errors.hasErrors()) {
            redirectAttributes.addFlashAttribute("errors", errors);
            redirectAttributes.addFlashAttribute("groupDeleteForm", groupDeleteForm);
        }
        return "redirect:/admin/groups/";
    }

}