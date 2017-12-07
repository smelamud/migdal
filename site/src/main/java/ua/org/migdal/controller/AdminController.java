package ua.org.migdal.controller;

import javax.inject.Inject;

import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import ua.org.migdal.location.LocationInfo;

@Controller
public class AdminController {

    @Inject
    private IndexController indexController;

    @GetMapping("/admin")
    public String admin(Model model) {
        adminLocationInfo(model);

        return "redirect:/admin/moderator";
    }

    public LocationInfo adminLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/admin")
                .withParent(indexController.indexLocationInfo(null))
                .withPageTitle("Администратор");
    }

}