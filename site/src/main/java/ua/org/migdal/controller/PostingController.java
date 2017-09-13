package ua.org.migdal.controller;

import java.util.HashMap;
import java.util.List;
import java.util.Map;

import javax.inject.Inject;
import javax.validation.Valid;

import org.springframework.stereotype.Controller;
import org.springframework.transaction.PlatformTransactionManager;
import org.springframework.ui.Model;
import org.springframework.validation.Errors;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.ModelAttribute;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.servlet.mvc.support.RedirectAttributes;
import org.springframework.web.util.UriComponentsBuilder;

import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.data.Posting;
import ua.org.migdal.data.Topic;
import ua.org.migdal.form.AdminPostingsForm;
import ua.org.migdal.form.PostingForm;
import ua.org.migdal.grp.GrpEnum;
import ua.org.migdal.manager.IdentManager;
import ua.org.migdal.manager.PostingManager;
import ua.org.migdal.manager.TopicManager;
import ua.org.migdal.session.LocationInfo;
import ua.org.migdal.session.RequestContext;

@Controller
public class PostingController {

    @Inject
    private PlatformTransactionManager txManager;

    @Inject
    private RequestContext requestContext;

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

    @GetMapping("/admin/postings")
    public String adminPostings(
            @ModelAttribute AdminPostingsForm adminPostingsForm,
            @RequestParam(defaultValue="0") Integer offset,
            Model model) {
        adminPostingsLocationInfo(model);

        Long index1 = adminPostingsForm.isUseIndex1() ? adminPostingsForm.getIndex1() : null;

        model.addAttribute("topicNames", topicManager.begNames(0, -1, false, false));
        model.addAttribute("adminPostingsForm", adminPostingsForm);
        model.addAttribute("postings",
                fetchAncestors(
                    postingManager.begAll(
                            adminPostingsForm.getTopicRoots(),
                            adminPostingsForm.getGrps(),
                            index1,
                            offset,
                            20)));
        model.addAttribute("postingsTotal",
                postingManager.countAll(
                        adminPostingsForm.getTopicRoots(),
                        adminPostingsForm.getGrps(),
                        index1));
        return "admin-postings";
    }

    private Iterable<Posting> fetchAncestors(Iterable<Posting> postings) {
        Map<Long, List<Topic>> ancestorMap = new HashMap<>();
        for (Posting posting : postings) {
            long topicId = posting.getParent().getId();
            if (!ancestorMap.containsKey(topicId)) {
                List<Topic> ancestors = topicManager.begAncestors(topicId);
                ancestorMap.put(topicId, ancestors);
            }
            posting.setAncestors(ancestorMap.get(topicId));
        }
        return postings;
    }

    public LocationInfo adminPostingsLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/admin/postings")
                .withTopics("topics-admin")
                .withTopicsIndex("admin-postings")
                .withParent(indexController.indexLocationInfo(null))
                .withPageTitle("Сообщения");
    }

    @GetMapping("/admin/postings/add")
    public String postingAdd(@RequestParam boolean full, Model model) {
        postingAddLocationInfo(model);

        return postingAddOrEdit(null, full, model);
    }

    public LocationInfo postingAddLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/admin/postings/add")
                .withParent(adminPostingsLocationInfo(null))
                .withPageTitle("Добавление сообщения");
    }

    @GetMapping("/admin/postings/{id}/edit")
    public String postingEdit(@PathVariable long id, @RequestParam boolean full, Model model)
            throws PageNotFoundException {
        Posting posting = postingManager.beg(id);
        if (posting == null) {
            throw new PageNotFoundException();
        }

        postingEditLocationInfo(id, model);

        return postingAddOrEdit(posting, full, model);
    }

    public LocationInfo postingEditLocationInfo(long id, Model model) {
        return new LocationInfo(model)
                .withUri("/admin/postings/" + id + "/edit")
                .withParent(adminPostingsLocationInfo(null))
                .withPageTitle("Редактирование сообщения");
    }

    private String postingAddOrEdit(Posting posting, boolean full, Model model) {
        model.addAttribute("xmlid", posting != null && full ? posting.getId() : 0);
        if (posting == null) {
            model.asMap().computeIfAbsent("postingForm", key -> new PostingForm(full, grpEnum.grpValue("NEWS")));
        } else {
            model.asMap().computeIfAbsent("postingForm", key -> new PostingForm(posting, full));
        }
        PostingForm postingForm = (PostingForm) model.asMap().get("postingForm");
        String rootIdent = postingForm.getGrpInfo().getRootIdent();
        long rootId = full || rootIdent == null ? 0 : identManager.getIdByIdent(rootIdent);
        model.addAttribute("topicNames", topicManager.begNames(rootId, -1, false, true));
        return "posting-edit";
    }

    @PostMapping("/actions/posting/modify")
    public String actionPostingModify(
            @ModelAttribute @Valid PostingForm postingForm,
            Errors errors,
            RedirectAttributes redirectAttributes) {
        Posting posting;
        if (postingForm.getId() <= 0) {
            posting = new Posting();
        } else {
            posting = postingManager.beg(postingForm.getId());
        }

        new ControllerAction(PostingController.class, "actionPostingModify", errors)
                .transactional(txManager)
                .constraint("entries_ident_key", "ident.used")
                .execute(() -> {
                    if (postingForm.getId() > 0) {
                        if (posting == null) {
                            return "noPosting";
                        }
                        if (!posting.isWritable()) {
                            return "notEditable";
                        }
                    }

                    /*String errorCode = Topic.validateHierarchy(null, up, postingForm.getId());
                    if (errorCode != null) {
                        return errorCode;
                    }
                    if (!up.isAppendable()) {
                        return "upId.noAppend";
                    }

                    String oldTrack = posting.getTrack();
                    boolean trackChanged = postingForm.isTrackChanged(posting);
                    boolean catalogChanged = postingForm.isCatalogChanged(posting);

                    postingForm.toTopic(posting, up, user, group, requestContext);
                    postingManager.saveAndFlush(posting); // We need to have the record in DB and to know ID
                                                          // after this point

                    String newTrack = TrackUtils.track(posting.getId(), up.getTrack());
                    if (postingForm.getId() <= 0) {
                        trackManager.setTrackById(posting.getId(), newTrack);
                        String newCatalog = CatalogUtils.catalog(EntryType.TOPIC, posting.getId(), posting.getIdent(),
                                posting.getModbits(), up.getCatalog());
                        catalogManager.setCatalogById(posting.getId(), newCatalog);
                    }
                    if (trackChanged) {
                        trackManager.replaceTracks(oldTrack, newTrack);
                    }
                    if (catalogChanged) {
                        catalogManager.updateCatalogs(newTrack);
                    }*/

                    return null;
                });

        if (!errors.hasErrors()) {
            return "redirect:" + requestContext.getBack();
        } else {
            redirectAttributes.addFlashAttribute("errors", errors);
            redirectAttributes.addFlashAttribute("postingForm", postingForm);
            String location;
            if (postingForm.getId() <= 0) {
                location = "redirect:/admin/postings/add";
            } else {
                location = "redirect:/admin/postings/" + posting.getId() + "/edit";
            }
            return UriComponentsBuilder.fromUriString(location)
                    .queryParam("back", requestContext.getBack())
                    .toUriString();
        }
    }

}