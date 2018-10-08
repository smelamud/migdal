package ua.org.migdal.controller;

import java.util.Collections;
import java.util.List;
import javax.inject.Inject;

import org.springframework.data.util.Pair;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.data.Topic;
import ua.org.migdal.data.User;
import ua.org.migdal.grp.GrpEnum;
import ua.org.migdal.location.LocationInfo;
import ua.org.migdal.manager.IdentManager;
import ua.org.migdal.manager.PostingManager;
import ua.org.migdal.manager.TopicManager;

@Controller
public class PerUserController {

    @Inject
    private GrpEnum grpEnum;

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
        List<User> users = postingManager.getOwners(topic.getId());
        List<Pair<Long, Boolean>> topicRoots = Collections.singletonList(Pair.of(topic.getId(), false));
        long[] grps = grpEnum.group("GALLERY");
        users.forEach(u -> u.setPreview(postingManager.begRandom(topicRoots, grps, u.getId())));
        model.addAttribute("users", users);
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
