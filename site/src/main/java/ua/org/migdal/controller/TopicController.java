package ua.org.migdal.controller;

import javax.inject.Inject;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;

import org.springframework.web.bind.annotation.PathVariable;
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
    public String adminTopicsRoot(Model model) {
        return adminTopics(null, model);
    }

    @GetMapping("/admin/topics/**/{upId}")
    public String adminTopicsSubtree(@PathVariable long upId, Model model) {
        return adminTopics(upId, model);
    }

    private String adminTopics(Long upId, Model model) {
        adminTopicsLocationInfo(model);

        model.addAttribute("up", upId != null ? topicManager.beg(upId) : null);
        model.addAttribute("ancestors", upId != null ? topicManager.begAncestors(upId) : null);
        model.addAttribute("topicTree", new Tree<>(topicManager.begAll(upId != null ? upId : 0, true)));
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