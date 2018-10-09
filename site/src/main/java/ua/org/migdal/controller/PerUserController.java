package ua.org.migdal.controller;

import java.util.Collections;
import java.util.List;
import javax.inject.Inject;

import org.springframework.data.util.Pair;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.RequestParam;
import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.data.Topic;
import ua.org.migdal.data.User;
import ua.org.migdal.grp.GrpEnum;
import ua.org.migdal.location.LocationInfo;
import ua.org.migdal.manager.IdentManager;
import ua.org.migdal.manager.PostingManager;
import ua.org.migdal.manager.TopicManager;
import ua.org.migdal.manager.UserManager;
import ua.org.migdal.session.RequestContext;

@Controller
public class PerUserController {

    @Inject
    private GrpEnum grpEnum;

    @Inject
    private RequestContext requestContext;

    @Inject
    private IdentManager identManager;

    @Inject
    private TopicManager topicManager;

    @Inject
    private PostingManager postingManager;

    @Inject
    private UserManager userManager;

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

        return perUser(model, topic);
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

    @GetMapping("/taglit/{folder}")
    public String taglitUser(
            @PathVariable String folder,
            @RequestParam(defaultValue = "0") Integer offset,
            @RequestParam(defaultValue = "sent") String sort,
            Model model) throws PageNotFoundException {

        Topic topic = topicManager.beg(identManager.idOrIdent("taglit"));
        if (topic == null) {
            throw new PageNotFoundException();
        }
        User user = userManager.beg(userManager.idOrLogin(folder));
        if (user == null) {
            throw new PageNotFoundException();
        }

        taglitUserLocationInfo(user, model);

        return perUserUser(topic, user, offset, sort, model);
    }

    public LocationInfo taglitUserLocationInfo(User user, Model model) {
        return new LocationInfo(model)
                .withUri("/taglit/" + user.getFolder())
                .withRssHref("/rss/")
                .withTopics("topics-per-user")
                .withTopicsIndex(Long.toString(user.getId()))
                .withParent(taglitLocationInfo(null))
                .withPageTitle("Таглит - " + user.getFullName())
                .withPageTitleRelative(user.getFullName().toString());
    }

    @GetMapping("/veterans")
    public String veterans(Model model) throws PageNotFoundException {
        Topic topic = topicManager.beg(identManager.idOrIdent("veterans"));
        if (topic == null) {
            throw new PageNotFoundException();
        }

        veteransLocationInfo(model);

        return perUser(model, topic);
    }

    public LocationInfo veteransLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/veterans")
                .withRssHref("/rss/")
                .withTopics("topics-per-user")
                .withTopicsIndex("0")
                .withParent(indexController.indexLocationInfo(null))
                .withPageTitle("Уголок ветерана");
    }

    @GetMapping("/veterans/{folder}")
    public String veteransUser(
            @PathVariable String folder,
            @RequestParam(defaultValue = "0") Integer offset,
            @RequestParam(defaultValue = "sent") String sort,
            Model model) throws PageNotFoundException {

        Topic topic = topicManager.beg(identManager.idOrIdent("veterans"));
        if (topic == null) {
            throw new PageNotFoundException();
        }
        User user = userManager.beg(userManager.idOrLogin(folder));
        if (user == null) {
            throw new PageNotFoundException();
        }

        veteransUserLocationInfo(user, model);

        return perUserUser(topic, user, offset, sort, model);
    }

    public LocationInfo veteransUserLocationInfo(User user, Model model) {
        return new LocationInfo(model)
                .withUri("/veterans/" + user.getFolder())
                .withRssHref("/rss/")
                .withTopics("topics-per-user")
                .withTopicsIndex(Long.toString(user.getId()))
                .withParent(veteransLocationInfo(null))
                .withPageTitle("Уголок ветерана - " + user.getFullName())
                .withPageTitleRelative(user.getFullName().toString());
    }

    private String perUser(Model model, Topic topic) {
        model.addAttribute("topic", topic);
        addOwners(topic, model);
        earController.addEars(model);
        return "per-user";
    }

    private void addOwners(Topic topic, Model model) {
        List<User> users = postingManager.getOwners(topic.getId());
        List<Pair<Long, Boolean>> topicRoots = Collections.singletonList(Pair.of(topic.getId(), false));
        long[] grps = grpEnum.group("GALLERY");
        users.forEach(u -> u.setPreview(postingManager.begRandom(topicRoots, grps, u.getId())));
        model.addAttribute("users", users);
    }

    private String perUserUser(Topic topic, User user, Integer offset, String sort, Model model) {
        model.addAttribute("topic", topic);
        model.addAttribute("user", user);
        addOwners(topic, model);
        indexController.addPostings(
                "PERUSER_FORUMS",
                topic,
                user.getId(),
                new String[] { "PERUSER_FORUMS" },
                requestContext.getUserId() == user.getId() || requestContext.isUserModerator(),
                0,
                Integer.MAX_VALUE,
                model);
        indexController.addGallery("GALLERY", topic, user.getId(), offset, 20, sort, model);
        earController.addEars(model);
        return "per-user-user";
    }

}
