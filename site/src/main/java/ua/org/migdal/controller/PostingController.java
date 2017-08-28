package ua.org.migdal.controller;

import javax.inject.Inject;

import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;

import ua.org.migdal.session.LocationInfo;

@Controller
public class PostingController {

    @Inject
    private IndexController indexController;

    @GetMapping("/admin/postings")
    public String adminPostings(Model model) {
        adminPostingsLocationInfo(model);

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