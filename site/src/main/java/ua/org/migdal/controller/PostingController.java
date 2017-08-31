package ua.org.migdal.controller;

import javax.inject.Inject;

import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;

import org.springframework.web.bind.annotation.ModelAttribute;
import org.springframework.web.bind.annotation.RequestParam;
import ua.org.migdal.form.AdminPostingsForm;
import ua.org.migdal.manager.PostingManager;
import ua.org.migdal.manager.TopicManager;
import ua.org.migdal.session.LocationInfo;

@Controller
public class PostingController {

    @Inject
    private TopicManager topicManager;

    @Inject
    private PostingManager postingManager;

    @Inject
    private IndexController indexController;

    @GetMapping("/admin/postings")
    public String adminPostings(
            @ModelAttribute AdminPostingsForm adminPostingsForm,
            @RequestParam(defaultValue="0") Integer offset,
            Model model) {
        adminPostingsLocationInfo(model);

        model.addAttribute("topicNames", topicManager.begNames(0, -1, false, false));
        model.addAttribute("adminPostingsForm", adminPostingsForm);
        model.addAttribute("postings",
                postingManager.begAll(
                        adminPostingsForm.getTopicRoots(),
                        adminPostingsForm.getGrps(),
                        adminPostingsForm.isUseIndex1() ? adminPostingsForm.getIndex1() : null,
                        offset,
                        20));
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