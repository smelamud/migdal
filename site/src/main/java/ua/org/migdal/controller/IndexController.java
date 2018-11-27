package ua.org.migdal.controller;

import java.sql.Timestamp;
import java.time.Duration;
import java.time.Instant;
import java.time.temporal.ChronoUnit;
import java.util.stream.Collectors;

import javax.inject.Inject;

import org.springframework.data.domain.Sort;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.RequestParam;

import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.data.CrossEntry;
import ua.org.migdal.data.LinkType;
import ua.org.migdal.data.Posting;
import ua.org.migdal.data.Topic;
import ua.org.migdal.grp.GrpEnum;
import ua.org.migdal.location.LocationInfo;
import ua.org.migdal.manager.CachedHtml;
import ua.org.migdal.manager.CrossEntryManager;
import ua.org.migdal.manager.HtmlCacheManager;
import ua.org.migdal.manager.IdentManager;
import ua.org.migdal.manager.PostingManager;
import ua.org.migdal.manager.Postings;
import ua.org.migdal.manager.TopicManager;
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
    private HtmlCacheManager htmlCacheManager;

    @Inject
    private PostingListController postingListController;

    @Inject
    private EarController earController;

    @Inject
    private EnglishController englishController;

    @GetMapping("/")
    public String index(
            @RequestParam(defaultValue = "0") Integer offset,
            Model model) throws PageNotFoundException {

        if (requestContext.isEnglish()) {
            return englishController.index(model);
        }

        indexLocationInfo(model);

        earController.addEars(model);
        addTextEars(model);
        addDailyAnnounce("migdal.events.kaitanot.5762.summer", model);
        postingListController.addPostings("TAPE", null, null, new String[] {"NEWS", "ARTICLES", "GALLERY", "BOOKS"},
                                          true, offset, 20, model);
        addHitParade(null, model);
        addDiscussions(model);
        return "index-www";
    }

    public LocationInfo indexLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/")
                .withRssHref("/rss")
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

        if (requestContext.isEnglish()) {
            return englishController.ident(ident, model);
        }

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
        postingListController.addSeeAlso(topic.getId(), model);
        postingListController.addPostings("TAPE", topic, null, new String[] {"NEWS", "ARTICLES", "GALLERY", "BOOKS"},
                                          true, offset, 20, model);
        addHitParade(topic.getId(), model);

        return "index-www";
    }

    public LocationInfo majorLocationInfo(Topic topic, Model model) {
        boolean withGallery = grpEnum.inGroup("GALLERY", topic.getGrp());
        return new LocationInfo(model)
                .withUri("/" + topic.getIdent())
                .withTopics(!withGallery ? "topics-major" : "topics-major-sub", new Posting(topic))
                .withTopicsIndex(!withGallery ? topic.getIdent() : "news")
                .withParent(indexLocationInfo(null))
                .withPageTitle(topic.getSubject());
    }

    @TopicsMapping("topics-major")
    protected void topicsMajor(Model model) {
        CachedHtml topicsMajorCache = htmlCacheManager.of("topicsMajor")
                                                      .ofTopicsIndex(model)
                                                      .during(Duration.ofHours(3))
                                                      .onTopics();
        model.addAttribute("topicsMajorCache", topicsMajorCache);
        if (topicsMajorCache.isInvalid()) {
            model.addAttribute("majors", crossEntryManager.getAll(LinkType.MAJOR, "major")
                                                          .stream()
                                                          .map(CrossEntry::getPeer)
                                                          .collect(Collectors.toList()));
        }
    }

    @TopicsMapping("topics-major-sub")
    protected void topicsMajorSub(Posting posting, Model model) {
        model.addAttribute("topic", posting.getTopic());
    }

    private void addTextEars(Model model) {
        CachedHtml textEarsCache = htmlCacheManager.of("textEars").onPostings();
        model.addAttribute("textEarsCache", textEarsCache);
        if (textEarsCache.isInvalid()) {
            model.addAttribute("textears", postingManager.begAll(Postings.all().grp("TEXTEARS").asGuest().limit(3)));
        }
    }

    private void addHitParade(Long topicId, Model model) {
        CachedHtml hitParadeCache = htmlCacheManager.of("hitParade").of(topicId).during(Duration.ofHours(1));
        model.addAttribute("hitParadeCache", hitParadeCache);
        if (hitParadeCache.isInvalid()) {
            Postings p = Postings.all()
                                 .topic(topicId, true)
                                 .grp("WRITINGS")
                                 .laterThan(Timestamp.from(Instant.now().minus(31, ChronoUnit.DAYS)))
                                 .asGuest()
                                 .limit(10)
                                 .sort(Sort.Direction.DESC, "rating");
            model.addAttribute("hitParade", postingManager.begAll(p));
        }
    }

    private void addDiscussions(Model model) {
        CachedHtml discussionsCache = htmlCacheManager.of("discussions")
                                                      .during(Duration.ofDays(1))
                                                      .onPostings()
                                                      .onComments();
        model.addAttribute("discussionsCache", discussionsCache);
        if (discussionsCache.isInvalid()) {
            model.addAttribute("discussions",
                    postingManager.begLastDiscussions(
                            grpEnum.group("DISCUSS"),
                            grpEnum.group("FORUMS"),
                            false,
                            0,
                            7));
        }
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
        postingListController.addSeeAlso(topic.getId(), model);
        postingListController.addGallery("GALLERY", topic, null, offset, 20, sort, model);

        return "gallery";
    }

    public LocationInfo majorGalleryLocationInfo(Topic topic, Model model) {
        return new LocationInfo(model)
                .withUri("/" + topic.getIdent() + "/gallery")
                .withTopics("topics-major-sub", new Posting(topic))
                .withTopicsIndex("gallery")
                .withParent(majorLocationInfo(topic, null))
                .withPageTitle(topic.getSubject() + " - Галерея")
                .withPageTitleRelative("Галерея");
    }

    private void addDailyAnnounce(String ident, Model model) {
        long topicId = identManager.idOrIdent(ident);
        CachedHtml dailyNewsCache = htmlCacheManager.of("dailyNews")
                                                    .of(topicId)
                                                    .ofRandom(10)
                                                    .during(Duration.ofHours(3))
                                                    .onPostings();
        model.addAttribute("dailyNewsCache", dailyNewsCache);
        if (dailyNewsCache.isInvalid()) {
            Topic topic = topicManager.beg(topicId);
            model.addAttribute("dailyNewsTopic", topic);

            Postings p = Postings.all()
                                 .topic(topicId)
                                 .grp("DAILY_NEWS")
                                 .sort(Sort.Direction.DESC, "index1")
                                 .limit(5);
            model.addAttribute("dailyNews", postingManager.begAll(p));

            p = Postings.all()
                        .topic(topicId)
                        .grp("GRAPHICS")
                        .sort(Sort.Direction.DESC, "index1")
                        .limit(20)
                        .asGuest();
            model.addAttribute("dailyPicture", postingManager.begRandomOne(p));
        }
    }

    @GetMapping("/api/help/text")
    public String helpText() {
        return "help-text";
    }

}
