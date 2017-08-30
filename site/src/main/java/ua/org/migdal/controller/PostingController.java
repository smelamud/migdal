package ua.org.migdal.controller;

import javax.inject.Inject;

import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;

import org.springframework.web.bind.annotation.ModelAttribute;
import ua.org.migdal.form.AdminPostingsForm;
import ua.org.migdal.manager.TopicManager;
import ua.org.migdal.session.LocationInfo;

@Controller
public class PostingController {

    @Inject
    private TopicManager topicManager;

    @Inject
    private IndexController indexController;

    @GetMapping("/admin/postings")
    public String adminPostings(@ModelAttribute AdminPostingsForm adminPostingsForm, Model model) {
        adminPostingsLocationInfo(model);

        model.addAttribute("topicNames", topicManager.begNames(0, -1, false, false));
        model.addAttribute("adminPostingsForm", adminPostingsForm);
        return "admin-postings";
    }

    public LocationInfo adminPostingsLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/admin/postings")
                .withTopics("topics-admin")
                .withTopicsIndex("admin-postings")
                .withParent(indexController.indexLocationInfo(null))
                .withPageTitle("Сообщения");
    }

}