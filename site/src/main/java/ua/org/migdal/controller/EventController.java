package ua.org.migdal.controller;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

import javax.inject.Inject;

import org.springframework.data.domain.Sort;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.RequestParam;

import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.data.EntryType;
import ua.org.migdal.data.Posting;
import ua.org.migdal.data.Topic;
import ua.org.migdal.data.util.TreeNode;
import ua.org.migdal.grp.GrpEnum;
import ua.org.migdal.location.LocationInfo;
import ua.org.migdal.manager.CachedHtml;
import ua.org.migdal.manager.HtmlCacheManager;
import ua.org.migdal.manager.IdentManager;
import ua.org.migdal.manager.PostingManager;
import ua.org.migdal.manager.Postings;
import ua.org.migdal.manager.TopicManager;
import ua.org.migdal.session.RequestContext;
import ua.org.migdal.util.CatalogUtils;

@Controller
public class EventController {

    private static final Pattern DAY_PATTERN = Pattern.compile("^day-(\\d+)/$");

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
    private HtmlCacheManager htmlCacheManager;

    @Inject
    private MigdalController migdalController;

    @Inject
    private EntryController entryController;

    @Inject
    private IndexController indexController;

    @Inject
    private EarController earController;

    @Inject
    private PostingViewController postingViewController;

    @Inject
    private PostingEditingController postingEditingController;

    @GetMapping("/migdal/events")
    public String events(Model model) throws PageNotFoundException {
        long eventsId = identManager.idOrIdent("migdal.events");
        if (eventsId <= 0) {
            throw new PageNotFoundException();
        }

        eventsLocationInfo(model);

        CachedHtml eventsCache = htmlCacheManager.of("events").ofRandom(10).onTopics();
        model.addAttribute("eventsCache", eventsCache);
        if (eventsCache.isInvalid()) {
            Iterable<Topic> events = topicManager.begAll(eventsId, true, Sort.Direction.DESC, "index2", "index0");

            List<Topic> lastEvents = new ArrayList<>();
            List<TreeNode<Topic>> eventGroups = new ArrayList<>();
            Map<Long, TreeNode<Topic>> eventGroupMap = new HashMap<>();

            for (Topic topic : events) {
                if (lastEvents.size() < 5) {
                    lastEvents.add(topic);
                }
                if (topic.getUpId() == eventsId) {
                    TreeNode<Topic> node = new TreeNode<>(topic.getId(), topic);
                    eventGroups.add(node);
                    eventGroupMap.put(topic.getId(), node);
                }
            }

            model.addAttribute("lastEvents", lastEvents);

            for (Topic topic : events) {
                if (topic.getUpId() != eventsId) {
                    TreeNode<Topic> group = eventGroupMap.get(topic.getUpId());
                    if (group != null) {
                        group.getChildren().add(new TreeNode<>(topic.getId(), topic));
                    }
                }
            }
            eventGroups.sort(
                    (node1, node2) -> node1.getElement().getSubject()
                            .compareToIgnoreCase(node2.getElement().getSubject()));
            model.addAttribute("eventGroups", eventGroups);

            Postings p = Postings.all().grp("GRAPHICS").asGuest();
            lastEvents.forEach(t -> p.topic(t.getId()));
            model.addAttribute("picture", postingManager.begRandomOne(p));
        }
        earController.addEars(model);

        return "events";
    }

    public LocationInfo eventsLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/migdal/events")
                .withTopics("topics-events")
                .withTopicsIndex("migdal.events")
                .withParent(migdalController.migdalLocationInfo(null))
                .withPageTitle("События")
                .withTranslationHref("/events");
    }

    @TopicsMapping("topics-events")
    protected void topicsEvents(Model model) {
        CachedHtml topicsEventsCache = htmlCacheManager.of("topicsEvents").ofTopicsIndex(model).onTopics();
        model.addAttribute("topicsEventsCache", topicsEventsCache);
        if (topicsEventsCache.isInvalid()) {
            long eventsId = identManager.idOrIdent("migdal.events");
            Iterable<Topic> allTopics = topicManager.begAll(eventsId, true, Sort.Direction.DESC, "index2", "index0");
            List<Topic> events = new ArrayList<>();
            for (Topic topic : allTopics) {
                if (topic.getId() != eventsId && topic.getUpId() != eventsId) {
                    events.add(topic);
                }
            }
            model.addAttribute("events", events);
        }
    }

    @GetMapping("/migdal/events/reorder")
    public String eventsReorder(
            @RequestParam Long year,
            Model model) {

        eventsReorderLocationInfo(model);

        Iterable<Topic> topics = topicManager.begAll(
                identManager.idOrIdent("migdal.events"), true, year, Sort.Direction.ASC, "index0");
        return entryController.entryReorder(topics, EntryType.TOPIC, model);
    }

    public LocationInfo eventsReorderLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/migdal/printings/reorder")
                .withParent(eventsLocationInfo(null))
                .withPageTitle("Расстановка событий");
    }

    @GetMapping("/migdal/events/{type}")
    public String eventsType() {
        return "redirect:/migdal/events";
    }

    @GetMapping("/migdal/events/{type}/{id}")
    public String eventsTypeId(
            @PathVariable String type,
            @PathVariable String id,
            @RequestParam(defaultValue = "0") Integer offset,
            Model model) {

        Topic topic = topicManager.beg(identManager.idOrIdent(String.format("migdal.events.%s.%s", type, id)));
        if (topic == null) {
            return "redirect:/migdal/events";
        }

        return regularEvent(topic, offset, model);
    }

    @GetMapping("/migdal/events/{type}/{id}/gallery")
    public String eventsTypeIdGallery(
            @PathVariable String type,
            @PathVariable String id,
            @RequestParam(defaultValue = "0") Integer offset,
            @RequestParam(defaultValue = "sent") String sort,
            Model model) throws PageNotFoundException {

        Topic topic = topicManager.beg(identManager.idOrIdent(String.format("migdal.events.%s.%s", type, id)));
        if (topic == null) {
            throw new PageNotFoundException();
        }

        return regularEventGallery(topic, offset, sort, model);
    }

    @GetMapping("/migdal/events/{type}/{id}/reorder")
    public String eventsTypeIdReorder(
            @PathVariable String type,
            @PathVariable String id,
            Model model) throws PageNotFoundException {

        Topic topic = topicManager.beg(identManager.idOrIdent(String.format("migdal.events.%s.%s", type, id)));
        if (topic == null || !topic.accepts("DAILY_NEWS")) {
            throw new PageNotFoundException();
        }

        return regularEventReorder(topic, model);
    }

    // @GetMapping("/migdal/events/{type}/{subtype}/{id}")
    public String eventsTypeSubtypeId(
            String type,
            String subtype,
            String id,
            Integer offset,
            Long tid,
            Model model) {

        Topic topic = topicManager.beg(
                identManager.idOrIdent(String.format("migdal.events.%s.%s.%s", type, subtype, id)));
        if (topic == null) {
            try {
                return postingViewController.postingView(model, offset, tid);
            } catch (PageNotFoundException e) {
                return "redirect:/migdal/events";
            }
        }

        return regularEvent(topic, offset, model);
    }

    @GetMapping("/migdal/events/{type}/{subtype}/{id}/gallery")
    public String eventsTypeIdGallery(
            @PathVariable String type,
            @PathVariable String subtype,
            @PathVariable String id,
            @RequestParam(defaultValue = "0") Integer offset,
            @RequestParam(defaultValue = "sent") String sort,
            Model model) throws PageNotFoundException {

        Topic topic = topicManager.beg(
                identManager.idOrIdent(String.format("migdal.events.%s.%s.%s", type, subtype, id)));
        if (topic == null) {
            throw new PageNotFoundException();
        }

        return regularEventGallery(topic, offset, sort, model);
    }

    @GetMapping("/migdal/events/{type}/{subtype}/{id}/reorder")
    public String eventsTypeIdReorder(
            @PathVariable String type,
            @PathVariable String subtype,
            @PathVariable String id,
            Model model) throws PageNotFoundException {

        Topic topic = topicManager.beg(
                identManager.idOrIdent(String.format("migdal.events.%s.%s.%s", type, subtype, id)));
        if (topic == null || !topic.accepts("DAILY_NEWS")) {
            throw new PageNotFoundException();
        }

        return regularEventReorder(topic, model);
    }

    private String regularEvent(Topic topic, Integer offset, Model model) {
        if (topic.accepts("DAILY_NEWS")) {
            return "redirect:" + topic.getHref() + "day-1";
        }

        regularEventLocationInfo(topic, model);

        model.addAttribute("topic", topic);
        indexController.addPostings("EVENT", topic, null, new String[] {"NEWS", "ARTICLES", "GALLERY", "BOOKS"},
                topic.isPostable(), false, offset, 20, model);
        indexController.addSeeAlso(topic.getId(), model);
        earController.addEars(model);

        return "migdal-news";
    }

    public LocationInfo regularEventLocationInfo(Topic topic, Model model) {
        return new LocationInfo(model)
                .withUri(topic.getHref())
                .withTopics("topics-event", new Posting(topic))
                .withTopicsIndex("news")
                .withParent(eventsLocationInfo(null))
                .withPageTitle(topic.getSubject())
                .withPageTitleFull("События :: " + topic.getSubject());
    }

    @TopicsMapping("topics-event")
    protected void topicsEvent(Posting posting, Model model) {
        model.addAttribute("topic", posting.getTopic());
    }

    private String regularEventGallery(Topic topic, Integer offset, String sort, Model model)
            throws PageNotFoundException {

        regularEventGalleryLocationInfo(topic, model);

        indexController.addGallery("GALLERY", topic, null, offset, 20, sort, model);
        earController.addEars(model);

        return "gallery";
    }

    public LocationInfo regularEventGalleryLocationInfo(Topic topic, Model model) {
        return new LocationInfo(model)
                .withUri(topic.getHref() + "gallery")
                .withTopics("topics-event", new Posting(topic))
                .withTopicsIndex("gallery")
                .withParent(regularEventLocationInfo(topic, null))
                .withPageTitle(topic.getSubject() + " - Галерея")
                .withPageTitleRelative("Галерея")
                .withPageTitleFull("События :: " + topic.getSubject() + " - Галерея");
    }

    private String regularEventReorder(Topic topic, Model model) {
        regularEventReorderLocationInfo(topic, model);

        Postings p = Postings.all()
                .topic(topic.getId())
                .grp("ARTICLES")
                .sort(Sort.Direction.ASC, "index0");
        Iterable<Posting> postings = postingManager.begAll(p);
        return entryController.entryReorder(postings, EntryType.POSTING, model);
    }

    public LocationInfo regularEventReorderLocationInfo(Topic topic, Model model) {
        return new LocationInfo(model)
                .withUri(topic.getHref() + "reorder")
                .withParent(regularEventLocationInfo(topic, null)) // does not matter
                .withPageTitle("Расстановка статей");
    }

    /**
     * @return null, if the catalog does not represent any valid daily news posting;
     *         dummy Posting object (with id == 0), if the catalog is valid,
     *             but the corresponding posting does not exist;
     *         valid Posting object, if it exists.
     */
    Posting begDailyNewsPosting(String catalog) {
        String dayName = CatalogUtils.sub(catalog, -1, 1);
        Matcher m = DAY_PATTERN.matcher(dayName);
        if (!m.matches()) {
            return null;
        }
        long day;
        try {
            day = Long.parseLong(m.group(1));
        } catch (NumberFormatException e) {
            return null;
        }

        long topicId = identManager.idOrIdent(CatalogUtils.toIdent(CatalogUtils.sub(catalog, 0, -1)));
        Topic topic = topicManager.beg(topicId);
        if (topic == null) {
            return null;
        }

        Postings p = Postings.all().topic(topicId).grp("DAILY_NEWS").index1(day);
        Posting posting = postingManager.begFirst(p);
        if (posting == null) {
            posting = new Posting(topic);
            posting.setCatalog(topic.getCatalog());
            posting.setGrp(grpEnum.grpValue("DAILY_NEWS"));
            posting.setIndex1(day);
        }
        return posting;
    }

    @TopicsMapping("topics-daily")
    protected void topicsDaily(Posting posting, Model model) {
        model.addAttribute("event", posting.getTopic());
        CachedHtml topicsDailyCache = htmlCacheManager.of("topicsDaily")
                                                      .of(posting.getTopicId())
                                                      .ofTopicsIndex(model)
                                                      .onPostings();
        model.addAttribute("topicsDailyCache", topicsDailyCache);
        if (topicsDailyCache.isInvalid()) {
            Postings p = Postings.all()
                                 .topic(posting.getTopicId())
                                 .grp("ARTICLES")
                                 .asGuest()
                                 .sort(Sort.Direction.ASC, "index0");
            model.addAttribute("allArticles", postingManager.begAll(p));
            p = Postings.all()
                        .topic(posting.getTopicId())
                        .grp("DAILY_NEWS")
                        .asGuest()
                        .sort(Sort.Direction.ASC, "index1");
            model.addAttribute("allDailyNews", postingManager.begAll(p));
        }
    }

    @DetailsMapping("daily-news")
    protected void dailyNews(Posting posting, Model model) {
        model.addAttribute("event", posting.getTopic());
        Postings p = Postings.all().topic(posting.getTopicId()).grp("DAILY_GALLERY").index1(posting.getIndex1());
        Iterable<Posting> pictures = postingManager.begAll(p);
        for (Posting picture : pictures) {
            requestContext.addOgImage(picture.getImageUrl());
        }
        model.addAttribute("pictures", pictures);
        model.addAttribute("prevDay",
                postingManager.begNextByIndex1(posting.getTopicId(), "DAILY_NEWS", posting.getIndex1(), false));
        model.addAttribute("nextDay",
                postingManager.begNextByIndex1(posting.getTopicId(), "DAILY_NEWS", posting.getIndex1(), true));
    }

    public LocationInfo dailyEventLocationInfo(Posting posting, Model model) {
        return new LocationInfo(model)
                .withUri(posting.getTopic().getHref())
                .withTopics("topics-daily", posting)
                .withTopicsIndex(Long.toString(posting.getId()))
                .withParent(eventsLocationInfo(null))
                .withPageTitle(posting.getTopic().getSubject())
                .withPageTitleFull("События :: " + posting.getTopic().getSubject());
    }

    public LocationInfo dailyEventNewsLocationInfo(Posting posting, Model model) {
        Postings p = Postings.all().topic(posting.getTopicId()).grp("DAILY_NEWS").index1(posting.getIndex1());
        Posting daily = postingManager.begFirst(p);
        String heading = daily != null ? daily.getHeading() : String.format("День %d", posting.getIndex1());
        return new LocationInfo(model)
                .withUri(String.format("%sday-%d", posting.getTopic().getHref(), posting.getIndex1()))
                .withTopics("topics-daily", posting)
                .withTopicsIndex(Long.toString(posting.getIndex1()))
                .withParent(dailyEventLocationInfo(posting, null))
                .withPageTitle(posting.getTopic().getSubject() + " - " + heading)
                .withPageTitleRelative(heading);
    }

    @GetMapping("/migdal/events/other/song-of-songs")
    public String songOfSongs(Model model) throws PageNotFoundException {
        Topic topic = topicManager.beg(identManager.idOrIdent("migdal.events.other.song-of-songs"));
        if (topic == null) {
            throw new PageNotFoundException();
        }

        songOfSongsLocationInfo(topic, model);

        model.addAttribute("topic", topic);
        Postings p = Postings.all().topic(topic.getId()).grp("REVIEWS").sort(Sort.Direction.ASC, "subject");
        model.addAttribute("reviews", postingManager.begAll(p));

        return "song-of-songs";
    }

    public LocationInfo songOfSongsLocationInfo(Topic topic, Model model) {
        return new LocationInfo(model)
                .withUri(topic.getHref())
                .withTopics("topics-events")
                .withTopicsIndex(Long.toString(topic.getId()))
                .withParent(eventsLocationInfo(null))
                .withPageTitle(topic.getSubject())
                .withPageTitleFull("События :: " + topic.getSubject());
    }

    @GetMapping("/migdal/events/other/song-of-songs/{id}")
    public String songOfSongsMember(@PathVariable long id, Model model) throws PageNotFoundException {
        Posting posting = postingManager.beg(id);
        if (posting == null) {
            throw new PageNotFoundException();
        }

        songOfSongsMemberLocationInfo(posting, model);

        postingViewController.addPostingView(model, posting, null, null);
        earController.addEars(model);

        return "migdal";
    }

    public LocationInfo songOfSongsMemberLocationInfo(Posting posting, Model model) {
        return new LocationInfo(model)
                .withUri(posting.getGrpDetailsHref())
                .withTopics("topics-events")
                .withTopicsIndex(Long.toString(posting.getTopic().getId()))
                .withParent(songOfSongsLocationInfo(posting.getTopic(), null))
                .withPageTitle(posting.getHeading())
                .withPageTitleFull("Песнь Песней :: " + posting.getHeading());
    }

    @GetMapping("/migdal/events/other/song-of-songs/add")
    public String songOfSongsMemberAdd(
            @RequestParam(required = false) boolean full,
            Model model) throws PageNotFoundException {

        return postingEditingController.postingAdd("reviews", full, model);
    }

}
