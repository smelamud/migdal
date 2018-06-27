package ua.org.migdal.controller;

import java.sql.Timestamp;
import java.time.Instant;
import java.time.temporal.ChronoUnit;
import java.util.ArrayList;
import java.util.Collections;
import java.util.List;
import java.util.stream.Collectors;

import javax.inject.Inject;

import org.springframework.data.domain.Sort;
import org.springframework.data.util.Pair;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.RequestParam;

import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.data.CrossEntry;
import ua.org.migdal.data.LinkType;
import ua.org.migdal.data.Topic;
import ua.org.migdal.grp.GrpDescriptor;
import ua.org.migdal.grp.GrpEnum;
import ua.org.migdal.location.GeneralViewFor;
import ua.org.migdal.location.GeneralViewPriority;
import ua.org.migdal.location.LocationInfo;
import ua.org.migdal.manager.CrossEntryManager;
import ua.org.migdal.manager.IdentManager;
import ua.org.migdal.manager.PostingManager;
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
    private EarController earController;

    @GetMapping("/")
    public String index(
            @RequestParam(defaultValue = "0") Integer offset,
            Model model) {

        indexLocationInfo(model);

        addMajors(model);
        earController.addEars(model);
        addTextEars(model);
        addPostings("TAPE", null, new String[] {"NEWS", "ARTICLES", "GALLERY", "BOOKS"}, true, offset, model);
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
        addMajors(model);
        earController.addEars(model);
        addSeeAlso(topic.getId(), model);
        addPostings("TAPE", topic, new String[] {"NEWS", "ARTICLES", "GALLERY", "BOOKS"}, true, offset, model);
        addHitParade(topic.getId(), model);
        return "index-www";
    }

    @GeneralViewFor(priority = GeneralViewPriority.MAJORS)
    public LocationInfo majorLocationInfo(Topic topic, Model model) {
        boolean withGallery = grpEnum.inGroup("GALLERY", topic.getGrp());
        return new LocationInfo(model)
                .withUri("/" + topic.getIdent())
                .withRssHref("/rss/")
                .withTopics(!withGallery ? "topics-major" : "topics-major-sub")
                .withTopicsIndex(!withGallery ? topic.getIdent() : "news")
                .withParent(indexLocationInfo(null))
                .withPageTitle(topic.getSubject());
    }

    public void addMajors(Model model) {
        model.addAttribute("majors", crossEntryManager.getAll(LinkType.MAJOR, "major")
                                                      .stream()
                                                      .map(CrossEntry::getPeer)
                                                      .collect(Collectors.toList()));
    }

    private void addSeeAlso(long id, Model model) {
        List<CrossEntry> links = crossEntryManager.getAll(LinkType.SEE_ALSO, id);
        model.addAttribute("seeAlsoVisible", links.size() > 0 || requestContext.isUserModerator());
        model.addAttribute("seeAlsoSourceId", id);
        model.addAttribute("seeAlsoLinks", links);

    }

    private void addTextEars(Model model) {
        model.addAttribute("textears", postingManager.begAll(null, grpEnum.group("TEXTEARS"), true, 0, 3));
    }

    private void addPostings(String groupName, Topic topic, String[] addGrpNames, boolean addVisible, Integer offset,
                             Model model) {
        model.addAttribute("postingsShowTopic", topic == null);
        model.addAttribute("postingsAddVisible", addVisible);
        model.addAttribute("postingsAddCatalog", topic != null ? topic.getCatalog() : "");
        List<Pair<Long, Boolean>> topicRoots = null;
        if (topic != null) {
            topicRoots = Collections.singletonList(Pair.of(topic.getId(), true));
        }
        model.addAttribute("postings",
                postingManager.begAll(
                        topicRoots,
                        grpEnum.group(groupName),
                        offset,
                        20,
                        Sort.Direction.DESC,
                        "priority",
                        "sent"));
        List<GrpDescriptor> addGrps = new ArrayList<>();
        if (addGrpNames != null) {
            for (String addGrpName : addGrpNames) {
                GrpDescriptor desc = grpEnum.grp(addGrpName);
                if (desc == null) {
                    continue;
                }
                if (topic != null && !topic.accepts(desc.getValue())) {
                    continue;
                }
                addGrps.add(desc);
            }
        }
        model.addAttribute("postingsAddGrps", addGrps);
    }

    private void addHitParade(Long topicId, Model model) {
        List<Pair<Long, Boolean>> topicRoots = null;
        if (topicId != null) {
            topicRoots = Collections.singletonList(Pair.of(topicId, true));
        }
        model.addAttribute("hitParade",
                postingManager.begAll(
                        topicRoots,
                        grpEnum.group("WRITINGS"),
                        null,
                        null,
                        true,
                        Timestamp.from(Instant.now().minus(31, ChronoUnit.DAYS)),
                        false,
                        0,
                        10,
                        Sort.Direction.DESC,
                        "rating"));
    }

    private void addDiscussions(Model model) {
        model.addAttribute("discussions",
                postingManager.begLastDiscussions(
                        grpEnum.group("DISCUSS"),
                        new long[]{grpEnum.grpValue("FORUMS")},
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

}