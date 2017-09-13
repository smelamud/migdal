package ua.org.migdal.controller;

import java.util.HashMap;
import java.util.List;
import java.util.Map;

import javax.inject.Inject;

import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.ModelAttribute;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.RequestParam;

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

@Controller
public class PostingController {

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

}