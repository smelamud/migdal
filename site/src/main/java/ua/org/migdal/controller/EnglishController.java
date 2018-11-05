package ua.org.migdal.controller;

import javax.inject.Inject;

import org.springframework.data.domain.Sort;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.RequestParam;
import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.data.Posting;
import ua.org.migdal.data.Topic;
import ua.org.migdal.location.LocationInfo;
import ua.org.migdal.manager.IdentManager;
import ua.org.migdal.manager.PostingManager;
import ua.org.migdal.manager.Postings;
import ua.org.migdal.manager.TopicManager;
import ua.org.migdal.session.RequestContext;

@Controller
public class EnglishController {

    @Inject
    private RequestContext requestContext;

    @Inject
    private TopicManager topicManager;

    @Inject
    private PostingManager postingManager;

    @Inject
    private IdentManager identManager;

    @Inject
    private IndexController indexController;

    @Inject
    private PostingViewController postingViewController;

    // @GetMapping("/")
    String index(Model model) throws PageNotFoundException {
        Posting posting = postingManager.beg(identManager.idOrIdent("post.migdal,e"));
        if (posting == null) {
            throw new PageNotFoundException();
        }

        indexLocationInfo(model);

        postingViewController.addPostingView(model, posting, null, null);

        return "index-english";
    }

    public LocationInfo indexLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/")
                .withRssHref("/rss/")
                .withTranslationHref("/")
                .withTopics("topics-migdal-english")
                .withTopicsIndex("index")
                .withPageTitle("Home");
    }

    @TopicsMapping("topics-migdal-english")
    protected void topicsMigdalEnglish(Model model) {
        Topic topic = topicManager.beg(identManager.idOrIdent("migdal,e"));
        model.addAttribute("migdal", topic);
        Postings p = Postings.all().topic(topic.getId()).grp("REVIEWS").sort(Sort.Direction.ASC, "index0").asGuest();
        model.addAttribute("allReviews", postingManager.begAll(p));
    }

    @GetMapping("/migdal/{id:^\\d+$}")
    public String migdal(@PathVariable long id, Model model) throws PageNotFoundException {
        Posting posting = postingManager.beg(id);
        if (posting == null) {
            throw new PageNotFoundException();
        }

        migdalLocationInfo(posting, model);

        postingViewController.addPostingView(model, posting, null, null);

        return "migdal";
    }

    // @GetMapping("/{ident}")
    public String ident(String ident, Model model) throws PageNotFoundException {
        if (ident.equals("migdal")) {
            return "redirect:/";
        }

        Posting posting = postingManager.beg(identManager.idOrIdent(String.format("post.%s,e", ident)));
        if (posting == null) {
            throw new PageNotFoundException();
        }

        migdalLocationInfo(posting, model);

        postingViewController.addPostingView(model, posting, null, null);

        return "migdal";
    }

    public LocationInfo migdalLocationInfo(Posting posting, Model model) {
        String translationHref = "/migdal";
        if (posting.getIdent() == null) {
            translationHref = null;
        } else if (posting.getIdent().equals("post.museum,e")) {
            translationHref = "/migdal/museum";
        } else if (posting.getIdent().equals("post.migdal-or,e")) {
            translationHref = "/migdal/migdal-or";
        } else if (posting.getIdent().equals("post.tzdaka,e")) {
            translationHref = "/migdal/tzdaka";
        } else if (posting.getIdent().equals("post.mazltov,e")) {
            translationHref = "/migdal/mazltov";
        }
        return new LocationInfo(model)
                .withUri("/migdal/" + posting.getId())
                .withTopics("topics-migdal-english")
                .withTopicsIndex(Long.toString(posting.getId()))
                .withParent(indexLocationInfo(null))
                .withPageTitle(posting.getHeading())
                .withTranslationHref(translationHref);
    }

    @GetMapping("/events")
    public String events(
            @RequestParam(defaultValue = "0") Integer offset,
            Model model) throws PageNotFoundException {

        Topic topic = topicManager.beg(identManager.idOrIdent("events,e"));
        if (topic == null) {
            throw new PageNotFoundException();
        }

        eventsLocationInfo(model);

        indexController.addPostings("TAPE", topic, null, new String[] {"NEWS", "ARTICLES", "BOOKS"},
                requestContext.isUserModerator(), offset, 20, model);

        return "events-english";
    }

    public LocationInfo eventsLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/events")
                .withTopics("topics-migdal-english")
                .withTopicsIndex("events")
                .withParent(indexLocationInfo(null))
                .withPageTitle("News & Events")
                .withTranslationHref("/migdal/news");
    }

    @GetMapping("/events/{id:^\\d+$}")
    public String event(@PathVariable long id, Model model) throws PageNotFoundException {
        Posting posting = postingManager.beg(id);
        if (posting == null) {
            throw new PageNotFoundException();
        }

        eventLocationInfo(posting, model);

        postingViewController.addPostingView(model, posting, null, null);

        return "posting";
    }

    public LocationInfo eventLocationInfo(Posting posting, Model model) {
        return new LocationInfo(model)
                .withUri("/events/" + posting.getId())
                .withTopics("topics-migdal-english")
                .withTopicsIndex("events")
                .withParent(eventsLocationInfo(null))
                .withPageTitle(posting.getHeading());
    }

}
