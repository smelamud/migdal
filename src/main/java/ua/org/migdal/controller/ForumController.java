package ua.org.migdal.controller;

import java.time.Duration;
import javax.inject.Inject;

import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestParam;

import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.grp.GrpEnum;
import ua.org.migdal.location.LocationInfo;
import ua.org.migdal.manager.CachedHtml;
import ua.org.migdal.manager.HtmlCacheManager;
import ua.org.migdal.manager.IdentManager;
import ua.org.migdal.manager.PostingManager;

@Controller
public class ForumController {

    @Inject
    private GrpEnum grpEnum;

    @Inject
    private PostingManager postingManager;

    @Inject
    private IdentManager identManager;

    @Inject
    private HtmlCacheManager htmlCacheManager;

    @Inject
    private IndexController indexController;

    @Inject
    private EarController earController;

    @Inject
    private PostingEditingController postingEditingController;

    @GetMapping("/forum")
    public String forums(
            @RequestParam(defaultValue = "0") Integer offset,
            Model model) {
        forumLocationInfo(model);

        CachedHtml forumsCache = htmlCacheManager.of("forums")
                                                 .of(offset)
                                                 .during(Duration.ofHours(3))
                                                 .onPostings()
                                                 .onComments();
        model.addAttribute("forumsCache", forumsCache);
        if (forumsCache.isInvalid()) {
            model.addAttribute("discussions",
                    postingManager.begLastDiscussions(
                            grpEnum.group("DISCUSS"),
                            grpEnum.group("FORUMS"),
                            true,
                            offset,
                            20));
        }
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

    @GetMapping("/forum/add")
    public String forumAdd(
            @RequestParam(required = false) boolean full,
            Model model) throws PageNotFoundException {

        forumAddLocationInfo(model);

        long topicId = identManager.idOrIdent("forum");
        return postingEditingController.postingAdd("FORUMS", topicId, null, full, model);
    }

    public LocationInfo forumAddLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/forum/add")
                .withParent(forumLocationInfo(null))
                .withPageTitle("Добавление темы для обсуждения");
    }

}
