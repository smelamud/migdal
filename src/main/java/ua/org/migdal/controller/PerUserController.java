package ua.org.migdal.controller;

import java.time.Duration;
import java.util.List;

import javax.inject.Inject;

import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;

import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.data.Topic;
import ua.org.migdal.data.User;
import ua.org.migdal.location.LocationInfo;
import ua.org.migdal.manager.CachedHtml;
import ua.org.migdal.manager.HtmlCacheManager;
import ua.org.migdal.manager.IdentManager;
import ua.org.migdal.manager.PostingManager;
import ua.org.migdal.manager.Postings;
import ua.org.migdal.manager.TopicManager;
import ua.org.migdal.manager.UserManager;
import ua.org.migdal.session.RequestContext;

@Controller
public class PerUserController {

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
    private HtmlCacheManager htmlCacheManager;

    @Inject
    private IndexController indexController;

    @Inject
    private PostingListController postingListController;

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
                .withTopics("topics-taglit")
                .withTopicsIndex("0")
                .withParent(indexController.indexLocationInfo(null))
                .withPageTitle("Таглит - Birthright")
                .withPageTitleRelative("Таглит");
    }

    // @GetMapping("/taglit/{folder}")
    public String taglitUser(String folder, Integer offset, String sort, Model model) throws PageNotFoundException {
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
                .withTopics("topics-taglit")
                .withTopicsIndex(Long.toString(user.getId()))
                .withParent(taglitLocationInfo(null))
                .withPageTitle("Таглит - " + user.getFullName())
                .withPageTitleRelative(user.getFullName().toString());
    }

    @TopicsMapping("topics-taglit")
    protected String topicsTaglit(Model model) {
        Topic topic = topicManager.beg(identManager.idOrIdent("taglit"));
        model.addAttribute("topic", topic);
        CachedHtml topicsPerUserCache = htmlCacheManager.of("topicsTaglit")
                                                        .ofTopicsIndex(model)
                                                        .during(Duration.ofHours(3))
                                                        .onPostings();
        model.addAttribute("topicsPerUserCache", topicsPerUserCache);
        if (topicsPerUserCache.isInvalid()) {
            model.addAttribute("users", postingManager.getOwners(topic.getId()));
        }

        return "topics-per-user";
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
                .withTopics("topics-veterans")
                .withTopicsIndex("0")
                .withParent(indexController.indexLocationInfo(null))
                .withPageTitle("Уголок ветерана");
    }

    // @GetMapping("/veterans/{folder}")
    public String veteransUser(String folder, Integer offset, String sort, Model model) throws PageNotFoundException {
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
                .withTopics("topics-veterans")
                .withTopicsIndex(Long.toString(user.getId()))
                .withParent(veteransLocationInfo(null))
                .withPageTitle("Уголок ветерана - " + user.getFullName())
                .withPageTitleRelative(user.getFullName().toString());
    }

    @TopicsMapping("topics-veterans")
    protected String topicsVeterans(Model model) {
        Topic topic = topicManager.beg(identManager.idOrIdent("veterans"));
        model.addAttribute("topic", topic);
        CachedHtml topicsPerUserCache = htmlCacheManager.of("topicsVeterans")
                                                        .ofTopicsIndex(model)
                                                        .during(Duration.ofHours(3))
                                                        .onPostings();
        model.addAttribute("topicsPerUserCache", topicsPerUserCache);
        if (topicsPerUserCache.isInvalid()) {
            model.addAttribute("users", postingManager.getOwners(topic.getId()));
        }

        return "topics-per-user";
    }

    private String perUser(Model model, Topic topic) {
        model.addAttribute("topic", topic);
        List<User> users = postingManager.getOwners(topic.getId());
        users.forEach(u -> {
            Postings p = Postings.all().topic(topic.getId()).grp("GALLERY").user(u.getId());
            u.setPreview(postingManager.begRandomOne(p));
        });
        model.addAttribute("users", users);
        earController.addEars(model);
        return "per-user";
    }

    private String perUserUser(Topic topic, User user, Integer offset, String sort, Model model) {
        model.addAttribute("topic", topic);
        model.addAttribute("user", user);
        postingListController.addPostings(
                "PERUSER_FORUMS",
                topic,
                user.getId(),
                new String[] { "PERUSER_FORUMS" },
                requestContext.getUserId() == user.getId() || requestContext.isUserModerator(),
                0,
                Integer.MAX_VALUE,
                model);
        postingListController.addGallery("GALLERY", topic, user.getId(), offset, 20, sort, model);
        earController.addEars(model);
        return "per-user-user";
    }

}
