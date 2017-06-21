package ua.org.migdal.controller;

import javax.inject.Inject;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;

import ua.org.migdal.data.util.Tree;
import ua.org.migdal.manager.TopicManager;
import ua.org.migdal.session.LocationInfo;

@Controller
public class TopicController {

    @Inject
    private TopicManager topicManager;

    @Inject
    private IndexController indexController;

    @GetMapping("/admin/topics")
    public String adminTopics(Model model) {
        adminTopicsLocationInfo(model);

        model.addAttribute("topicTree", new Tree<>(topicManager.getAll()));
        return "admin-topics";
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