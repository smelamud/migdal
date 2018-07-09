package ua.org.migdal.controller;

import javax.inject.Inject;

import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestParam;
import ua.org.migdal.grp.GrpEnum;
import ua.org.migdal.location.LocationInfo;
import ua.org.migdal.manager.PostingManager;

@Controller
public class ForumController {

    @Inject
    private GrpEnum grpEnum;

    @Inject
    private PostingManager postingManager;

    @Inject
    private IndexController indexController;

    @Inject
    private EarController earController;

    @GetMapping("/forum")
    public String forums(
            @RequestParam(defaultValue = "0") Integer offset,
            Model model) {
        forumLocationInfo(model);

        model.addAttribute("discussions",
                postingManager.begLastDiscussions(
                        grpEnum.group("DISCUSS"),
                        new long[]{grpEnum.grpValue("FORUMS")},
                        true,
                        offset,
                        20));
        indexController.addMajors(model);
        earController.addEars(model);

        return "forums";
    }

    public LocationInfo forumLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/forum")
                .withTopics("topics-major")
                .withTopicsIndex("forum")
                .withParent(indexController.indexLocationInfo(null))
                .withPageTitle("Все обсуждения");
    }

}
