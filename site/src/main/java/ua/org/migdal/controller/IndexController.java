package ua.org.migdal.controller;

import java.util.List;
import java.util.Set;
import java.util.stream.Collectors;

import javax.inject.Inject;

import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;

import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.data.CrossEntry;
import ua.org.migdal.data.LinkType;
import ua.org.migdal.data.Posting;
import ua.org.migdal.data.Topic;
import ua.org.migdal.data.VoteType;
import ua.org.migdal.grp.GrpEnum;
import ua.org.migdal.manager.CrossEntryManager;
import ua.org.migdal.manager.IdentManager;
import ua.org.migdal.manager.PostingManager;
import ua.org.migdal.manager.TopicManager;
import ua.org.migdal.manager.VoteManager;
import ua.org.migdal.session.LocationInfo;
import ua.org.migdal.session.RequestContext;

@Controller
public class IndexController {

    @Inject
    private RequestContext requestContext;

    @Inject
    private GrpEnum grpEnum;

    @Inject
    private IdentManager identManager;

    @Inject
    private CrossEntryManager crossEntryManager;

    @Inject
    private TopicManager topicManager;

    @Inject
    private PostingManager postingManager;

    @Inject
    private VoteManager voteManager;

    @GetMapping("/")
    public String index(Model model) {
        indexLocationInfo(model);

        addMajors(model);
        addEars(model);
        addTextEars(model);
        return "index-www";
    }

    public LocationInfo indexLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/")
                .withRssHref("/rss/")
                .withTranslationHref("/")
                .withTopics("topics-major")
                .withTopicsIndex("index")
                .withPageTitle("Главная");
    }

    @GetMapping("/{ident}")
    public String major(@PathVariable String ident, Model model) throws PageNotFoundException {
        long id = identManager.idOrIdent(ident);
        if (id <= 0) {
            throw new PageNotFoundException();
        }
        Topic topic = topicManager.beg(id);
        if (topic == null) {
            throw new PageNotFoundException();
        }

        majorLocationInfo(topic, model);

        model.addAttribute("topic", topic);
        addMajors(model);
        addSeeAlso(topic.getId(), model);
        addEars(model);
        return "index-www";
    }

    public LocationInfo majorLocationInfo(Topic topic, Model model) {
        boolean withGallery = grpEnum.inGroup("GALLERY", topic.getGrp());
        return new LocationInfo(model)
                .withUri("/" + topic.getIdent())
                .withRssHref("/rss/")
                .withTopics(!withGallery ? "topics-major" : "topics-major-sub")
                .withTopicsIndex(!withGallery ? topic.getIdent() : "news")
                .withPageTitle(topic.getSubject());
    }

    private void addMajors(Model model) {
        model.addAttribute("majors", crossEntryManager.getAll(LinkType.MAJOR, "major")
                                                      .stream()
                                                      .map(CrossEntry::getPeer)
                                                      .collect(Collectors.toList()));
    }

    private void addEars(Model model) {
        Set<Posting> ears = postingManager.begRandom(null, grpEnum.group("EARS"), 3);
        ears.forEach(posting -> {
            voteManager.vote(posting, VoteType.VIEW, 1);
            postingManager.save(posting);
        });
        model.addAttribute("ears", ears);
    }

    private void addSeeAlso(long id, Model model) {
        List<CrossEntry> links = crossEntryManager.getAll(LinkType.SEE_ALSO, id);
        model.addAttribute("seeAlsoVisible", links.size() > 0 || requestContext.isUserModerator());
        model.addAttribute("seeAlsoLinks", links);

    }

    private void addTextEars(Model model) {
        model.addAttribute("textears", postingManager.begAll(null, grpEnum.group("TEXTEARS"), true, 0, 3));
    }

}