package ua.org.migdal.controller;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.data.User;
import ua.org.migdal.manager.UsersManager;
import ua.org.migdal.session.LocationInfo;
import ua.org.migdal.session.RequestContext;

@Controller
public class UserController {

    @Autowired
    private RequestContext requestContext;

    @Autowired
    private UsersManager usersManager;

    @Autowired
    private IndexController indexController;

    @GetMapping("/users")
    public String users() {
        return requestContext.isUserAdminUsers() ? "redirect:/admin/users" : "redirect:/";
    }

    @GetMapping("/users/{folder}")
    public String userInfo(@PathVariable String folder, Model model) throws PageNotFoundException {
        User user = usersManager.get(usersManager.idOrLogin(folder));
        if (user == null) {
            throw new PageNotFoundException();
        }

        userInfoLocationInfo(folder, user.getLogin(), model);

        model.addAttribute("user", user);
        model.addAttribute("admin", false);
        return "userinfo";
    }

    public LocationInfo userInfoLocationInfo(String folder, String login, Model model) {
        return new LocationInfo(model)
                .withUri("/users/" + folder)
                .withParent(indexController.indexLocationInfo(null))
                .withPageTitle("О пользователе " + login);
    }


}
