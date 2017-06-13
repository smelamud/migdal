package ua.org.migdal.controller;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import ua.org.migdal.session.LocationInfo;

@Controller
public class TopicController {

    @Autowired
    private IndexController indexController;

    @GetMapping("/admin/topics")
    public String adminTopics(Model model) {
        adminTopicsLocationInfo(model);

        return "admin-groups";
    }

    public LocationInfo adminTopicsLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/admin/topics")
                .withTopics("topics-admin")
                .withTopicsIndex("admin-topics")
                .withParent(indexController.indexLocationInfo(null))
                .withPageTitle("Темы");
    }

}