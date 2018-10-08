package ua.org.migdal.controller;

import javax.inject.Inject;

import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.data.Topic;
import ua.org.migdal.location.LocationInfo;
import ua.org.migdal.manager.IdentManager;
import ua.org.migdal.manager.PostingManager;
import ua.org.migdal.manager.TopicManager;

@Controller
public class PerUserController {

    @Inject
    private IdentManager identManager;

    @Inject
    private TopicManager topicManager;

    @Inject
    private PostingManager postingManager;

    @Inject
    private IndexController indexController;

    @Inject
    private EarController earController;

    @GetMapping("/taglit")
    public String taglit(Model model) throws PageNotFoundException {
        Topic topic = topicManager.beg(identManager.idOrIdent("taglit"));
        if (topic == null) {
            throw new PageNotFoundException();
        }

        taglitLocationInfo(model);

        model.addAttribute("topic", topic);
        model.addAttribute("users", postingManager.getOwners(topic.getId()));
        earController.addEars(model);
        return "per-user";
    }

    public LocationInfo taglitLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/taglit")
                .withRssHref("/rss/")
                .withTopics("topics-per-user")
                .withTopicsIndex("0")
                .withParent(indexController.indexLocationInfo(null))
                .withPageTitle("Таглит - Birthright")
                .withPageTitleRelative("Таглит");
    }

}
