package ua.org.migdal.controller;

import java.sql.Timestamp;
import java.time.Instant;
import java.time.temporal.ChronoUnit;
import java.util.ArrayList;
import java.util.List;
import java.util.stream.Collectors;

import javax.inject.Inject;

import org.springframework.data.domain.PageImpl;
import org.springframework.data.domain.PageRequest;
import org.springframework.data.domain.Sort;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.RequestParam;

import ua.org.migdal.Config;
import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.data.CrossEntry;
import ua.org.migdal.data.LinkType;
import ua.org.migdal.data.Posting;
import ua.org.migdal.data.Topic;
import ua.org.migdal.grp.GrpDescriptor;
import ua.org.migdal.grp.GrpEnum;
import ua.org.migdal.location.LocationInfo;
import ua.org.migdal.manager.CrossEntryManager;
import ua.org.migdal.manager.IdentManager;
import ua.org.migdal.manager.PostingManager;
import ua.org.migdal.manager.Postings;
import ua.org.migdal.manager.TopicManager;
import ua.org.migdal.session.RequestContext;

@Controller
public class IndexController {

    @Inject
    private Config config;

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
    private EarController earController;

    @GetMapping("/")
    public String index(
            @RequestParam(defaultValue = "0") Integer offset,
            Model model) {

        indexLocationInfo(model);

        earController.addEars(model);
        addTextEars(model);
        addPostings("TAPE", null, null, new String[] {"NEWS", "ARTICLES", "GALLERY", "BOOKS"}, true, offset, 20, model);
        addHitParade(null, model);
        addDiscussions(model);
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
    public String major(
            @PathVariable String ident,
            @RequestParam(defaultValue = "0") Integer offset,
            Model model) throws PageNotFoundException {

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
        earController.addEars(model);
        addSeeAlso(topic.getId(), model);
        addPostings("TAPE", topic, null, new String[] {"NEWS", "ARTICLES", "GALLERY", "BOOKS"}, true, offset, 20, model);
        addHitParade(topic.getId(), model);

        return "index-www";
    }

    public LocationInfo majorLocationInfo(Topic topic, Model model) {
        boolean withGallery = grpEnum.inGroup("GALLERY", topic.getGrp());
        return new LocationInfo(model)
                .withUri("/" + topic.getIdent())
                .withRssHref("/rss/")
                .withTopics(!withGallery ? "topics-major" : "topics-major-sub", new Posting(topic))
                .withTopicsIndex(!withGallery ? topic.getIdent() : "news")
                .withParent(indexLocationInfo(null))
                .withPageTitle(topic.getSubject());
    }

    @TopicsMapping("topics-major")
    protected void topicsMajor(Model model) {
        model.addAttribute("majors", crossEntryManager.getAll(LinkType.MAJOR, "major")
                                                      .stream()
                                                      .map(CrossEntry::getPeer)
                                                      .collect(Collectors.toList()));
    }

    @TopicsMapping("topics-major-sub")
    protected void topicsMajorSub(Posting posting, Model model) {
        model.addAttribute("topic", posting.getTopic());
    }

    void addSeeAlso(long id, Model model) {
        List<CrossEntry> links = crossEntryManager.getAll(LinkType.SEE_ALSO, id);
        model.addAttribute("seeAlsoVisible", links.size() > 0 || requestContext.isUserModerator());
        model.addAttribute("seeAlsoSourceId", id);
        model.addAttribute("seeAlsoLinks", links);

    }

    private void addTextEars(Model model) {
        model.addAttribute("textears", postingManager.begAll(Postings.all().grp("TEXTEARS").asGuest().limit(3)));
    }

    void addPostings(String groupName, Topic topic, Long userId, String[] addGrpNames, boolean addVisible,
                     int offset, int limit, Model model) {
        boolean showTopic = topic == null;
        addPostings(groupName, topic, userId, addGrpNames, addVisible, showTopic, offset, limit, model);
    }

    void addPostings(String groupName, Topic topic, Long userId, String[] addGrpNames, boolean addVisible,
                     boolean showTopic, int offset, int limit, Model model) {
        model.addAttribute("postingsShowTopic", showTopic);
        model.addAttribute("postingsAddVisible", addVisible);
        model.addAttribute("postingsAddCatalog", topic != null ? topic.getCatalog() : "");
        Postings p = Postings.all()
                             .topic(topic != null ? topic.getId() : null, true)
                             .grp(groupName)
                             .user(userId)
                             .page(offset, limit)
                             .sort(Sort.Direction.DESC, "priority", "sent");
        Iterable<Posting> postings = postingManager.begAll(p);
        for (Posting posting : postings) {
            if (posting.isGrpPublisher()) {
                posting.setPublishedEntries(
                        crossEntryManager.getAll(LinkType.PUBLISH, posting.getId()).stream()
                            .map(CrossEntry::getPeer)
                            .collect(Collectors.toList()));
            }
        }
        model.addAttribute("postings", postings);
        List<GrpDescriptor> addGrps = new ArrayList<>();
        if (addGrpNames != null) {
            for (String addGrpName : addGrpNames) {
                GrpDescriptor desc = grpEnum.grp(addGrpName);
                if (desc == null) {
                    continue;
                }
                if (topic != null && !topic.accepts(addGrpName)) {
                    continue;
                }
                addGrps.add(desc);
            }
        }
        model.addAttribute("postingsAddGrps", addGrps);
    }

    private void addHitParade(Long topicId, Model model) {
        Postings p = Postings.all()
                             .topic(topicId, true)
                             .grp("WRITINGS")
                             .laterThan(Timestamp.from(Instant.now().minus(31, ChronoUnit.DAYS)))
                             .asGuest()
                             .limit(10)
                             .sort(Sort.Direction.DESC, "rating");
        model.addAttribute("hitParade", postingManager.begAll(p));
    }

    private void addDiscussions(Model model) {
        model.addAttribute("discussions",
                postingManager.begLastDiscussions(
                        grpEnum.group("DISCUSS"),
                        grpEnum.group("FORUMS"),
                        false,
                        0,
                        7));
    }

    @GetMapping("/major")
    public String crossEntries(Model model) {
        crossEntriesLocationInfo(model);

        model.addAttribute("sourceName", "major");
        model.addAttribute("linkType", LinkType.MAJOR.ordinal());
        model.addAttribute("crossEntries", crossEntryManager.getAll(LinkType.MAJOR, "major"));
        return "cross-entries";
    }

    public LocationInfo crossEntriesLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/major")
                .withParent(indexLocationInfo(model))
                .withPageTitle("Основные темы");
    }

    @GetMapping("/{ident}/gallery")
    public String majorGallery(
            @PathVariable String ident,
            @RequestParam(defaultValue = "0") Integer offset,
            @RequestParam(defaultValue = "sent") String sort,
            Model model) throws PageNotFoundException {

        long id = identManager.idOrIdent(ident);
        if (id <= 0) {
            throw new PageNotFoundException();
        }
        Topic topic = topicManager.beg(id);
        if (topic == null) {
            throw new PageNotFoundException();
        }

        majorGalleryLocationInfo(topic, model);

        model.addAttribute("topic", topic);
        earController.addEars(model);
        addSeeAlso(topic.getId(), model);
        addGallery("GALLERY", topic, null, offset, 20, sort, model);

        return "gallery";
    }

    void addGallery(String grpName, Topic topic, Long userId, int offset, int limit, String sort, Model model) {
        boolean addVisible = topic.accepts(grpName)
                && (requestContext.isLogged() || config.isAllowGuests() && topic.isGuestPostable())
                && (userId == null || requestContext.getUserId() == userId || requestContext.isUserModerator());
        model.addAttribute("galleryAddVisible", addVisible);
        model.addAttribute("galleryAddCatalog", topic.getCatalog());
        model.addAttribute("gallerySort", sort);

        if (!sort.equals("sent") && !sort.equals("rating")) { // The value comes from client, needs validation
            sort = "sent";
        }
        Postings p = Postings.all()
                             .topic(topic.getId(), true)
                             .grp(grpName)
                             .user(userId)
                             .sort(Sort.Direction.DESC, sort);
        List<Posting> postings = postingManager.begAllAsList(p);

        int galleryBegin = offset < 0 ? 0 : offset / limit * limit;
        int galleryEnd = offset + limit;
        galleryEnd = galleryEnd > postings.size() ? postings.size() : galleryEnd;
        model.addAttribute("galleryBegin", galleryBegin);
        model.addAttribute("galleryEnd", galleryEnd);
        model.addAttribute("gallery", postings);
        model.addAttribute("galleryPage",
                new PageImpl<>(
                        postings.subList(galleryBegin, galleryEnd),
                        PageRequest.of(galleryBegin / limit, limit),
                        postings.size()));
    }

    public LocationInfo majorGalleryLocationInfo(Topic topic, Model model) {
        return new LocationInfo(model)
                .withUri("/" + topic.getIdent() + "/gallery")
                .withRssHref("/rss/")
                .withTopics("topics-major-sub", new Posting(topic))
                .withTopicsIndex("gallery")
                .withParent(majorLocationInfo(topic, null))
                .withPageTitle(topic.getSubject() + " - Галерея")
                .withPageTitleRelative("Галерея");
    }

}