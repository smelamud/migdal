package ua.org.migdal.controller;

import java.util.ArrayList;
import java.util.List;
import java.util.stream.Collectors;

import javax.inject.Inject;

import org.springframework.data.domain.PageImpl;
import org.springframework.data.domain.PageRequest;
import org.springframework.data.domain.Sort;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import ua.org.migdal.Config;
import ua.org.migdal.data.CrossEntry;
import ua.org.migdal.data.LinkType;
import ua.org.migdal.data.Posting;
import ua.org.migdal.data.Topic;
import ua.org.migdal.grp.GrpDescriptor;
import ua.org.migdal.grp.GrpEnum;
import ua.org.migdal.manager.CachedHtml;
import ua.org.migdal.manager.CrossEntryManager;
import ua.org.migdal.manager.HtmlCacheManager;
import ua.org.migdal.manager.PostingManager;
import ua.org.migdal.manager.Postings;
import ua.org.migdal.session.RequestContext;

@Controller
public class PostingListController {

    @Inject
    private Config config;

    @Inject
    private RequestContext requestContext;

    @Inject
    private GrpEnum grpEnum;

    @Inject
    private PostingManager postingManager;

    @Inject
    private CrossEntryManager crossEntryManager;

    @Inject
    private HtmlCacheManager htmlCacheManager;

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

    void addSeeAlso(long id, Model model) {
        CachedHtml seeAlsoCache = htmlCacheManager.of("seeAlso")
                                                  .of(id)
                                                  .of(requestContext.isUserModerator())
                                                  .onCrossEntries();
        model.addAttribute("seeAlsoCache", seeAlsoCache);
        if (seeAlsoCache.isInvalid()) {
            List<CrossEntry> links = crossEntryManager.getAll(LinkType.SEE_ALSO, id);
            model.addAttribute("seeAlsoVisible", links.size() > 0 || requestContext.isUserModerator());
            model.addAttribute("seeAlsoSourceId", id);
            model.addAttribute("seeAlsoLinks", links);
        }
    }

}
