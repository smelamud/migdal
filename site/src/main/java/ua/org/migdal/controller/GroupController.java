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

import ua.org.migdal.form.GroupAddForm;
import ua.org.migdal.manager.GroupManager;
import ua.org.migdal.session.LocationInfo;
import ua.org.migdal.session.RequestContext;

@Controller
public class GroupController {

    @Autowired
    private RequestContext requestContext;

    @Autowired
    private GroupManager groupManager;

    @Autowired
    private IndexController indexController;

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
                .withParent(indexController.indexLocationInfo(null))
                .withPageTitle("Группы");
    }

    @GetMapping("/admin/groups/add")
    public String adminGroupsAdd(@RequestParam(name="group_name", required=false) String groupName,
                                 @RequestParam(name="user_name", required=false) String userName,
                                 Model model) {
        adminGroupsAddLocationInfo(model);

        model.asMap().putIfAbsent("groupAddForm", new GroupAddForm(groupName, userName));
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

        if (!errors.hasErrors()) {
            return "redirect:/admin/groups/";
        } else {
            redirectAttributes.addFlashAttribute("errors", errors);
            redirectAttributes.addFlashAttribute("groupAddForm", groupAddForm);
            return "redirect:" + requestContext.getBack();
        }
    }

}