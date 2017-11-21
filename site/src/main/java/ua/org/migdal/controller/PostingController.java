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

import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.data.Posting;
import ua.org.migdal.data.PostingModbit;
import ua.org.migdal.data.Topic;
import ua.org.migdal.form.AdminModeratorForm;
import ua.org.migdal.form.AdminPostingsForm;
import ua.org.migdal.form.ModbitForm;
import ua.org.migdal.form.ModerateMassForm;
import ua.org.migdal.manager.PostingManager;
import ua.org.migdal.manager.TopicManager;
import ua.org.migdal.manager.UserManager;
import ua.org.migdal.session.LocationInfo;
import ua.org.migdal.session.RequestContext;

@Controller
public class PostingController {

    @Inject
    private PlatformTransactionManager txManager;

    @Inject
    private RequestContext requestContext;

    @Inject
    private TopicManager topicManager;

    @Inject
    private PostingManager postingManager;

    @Inject
    private UserManager userManager;

    @Inject
    private AdminController adminController;

    @Inject
    private PostingEditingController postingEditingController;

    @Inject
    private EntryController entryController;

    @GetMapping("/admin/postings")
    public String adminPostings(
            @ModelAttribute AdminPostingsForm adminPostingsForm,
            @RequestParam(defaultValue = "0") Integer offset,
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
                                null,
                                offset,
                                20)));
        model.addAttribute("postingsTotal",
                postingManager.countAll(
                        adminPostingsForm.getTopicRoots(),
                        adminPostingsForm.getGrps(),
                        index1,
                        null));
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
                .withParent(adminController.adminLocationInfo(null))
                .withPageTitle("Сообщения");
    }

    @GetMapping("/admin/postings/add")
    public String postingAdd(@RequestParam(required = false) boolean full, Model model) throws PageNotFoundException {
        postingAddLocationInfo(model);

        return postingEditingController.postingAddOrEdit(null, "NEWS", full, model);
    }

    public LocationInfo postingAddLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/admin/postings/add")
                .withParent(adminPostingsLocationInfo(null))
                .withPageTitle("Добавление сообщения");
    }

    @GetMapping("/admin/postings/{id}/edit")
    public String postingEdit(@PathVariable long id, @RequestParam(required = false) boolean full, Model model)
            throws PageNotFoundException {
        postingEditLocationInfo(id, model);

        return postingEditingController.postingAddOrEdit(id, "NEWS", full, model);
    }

    public LocationInfo postingEditLocationInfo(long id, Model model) {
        return new LocationInfo(model)
                .withUri("/admin/postings/" + id + "/edit")
                .withParent(adminPostingsLocationInfo(null))
                .withPageTitle("Редактирование сообщения");
    }

    @GetMapping("/admin/postings/{id}/chmod")
    public String postingChmod(@PathVariable long id, Model model) throws PageNotFoundException {
        Posting posting = postingManager.beg(id);
        if (posting == null) {
            throw new PageNotFoundException();
        }

        postingChmodLocationInfo(posting.getId(), model);

        return entryController.entryChmod(posting, model);
    }

    public LocationInfo postingChmodLocationInfo(long id, Model model) {
        return new LocationInfo(model)
                .withUri("/admin/postings/" + id + "chmod")
                .withParent(adminPostingsLocationInfo(null))
                .withPageTitle("Изменение прав на сообщение");
    }

    @GetMapping("/admin/postings/{id}/modbits")
    public String postingModbits(@PathVariable long id, Model model) throws PageNotFoundException {
        Posting posting = postingManager.beg(id);
        if (posting == null) {
            throw new PageNotFoundException();
        }

        postingModbitsLocationInfo(posting.getId(), model);

        model.asMap().computeIfAbsent("modbitForm", key -> new ModbitForm(posting));
        return "modbits";
    }

    public LocationInfo postingModbitsLocationInfo(long id, Model model) {
        return new LocationInfo(model)
                .withUri("/admin/postings/" + id + "modbits")
                .withParent(adminPostingsLocationInfo(null))
                .withPageTitle("Редактирование флагов сообщения");
    }

    @PostMapping("/actions/posting/modbits")
    public String actionModbits(
            @ModelAttribute @Valid ModbitForm modbitForm,
            Errors errors,
            RedirectAttributes redirectAttributes) {
        new ControllerAction(EntryController.class, "actionModbits", errors)
                .transactional(txManager)
                .execute(() -> {
                    Posting posting = postingManager.beg(modbitForm.getId());

                    if (posting == null) {
                        return "noPosting";
                    }
                    if (!posting.isWritable()) {
                        return "notModerator";
                    }

                    modbitForm.toPosting(posting);
                    postingManager.save(posting);

                    return null;
                });

        if (!errors.hasErrors()) {
            return "redirect:" + requestContext.getOrigin();
        } else {
            redirectAttributes.addFlashAttribute("errors", errors);
            redirectAttributes.addFlashAttribute("modbitForm", modbitForm);
            return "redirect:" + requestContext.getBack();
        }
    }

    @GetMapping("/admin/moderator")
    public String adminModerator(
            @ModelAttribute AdminModeratorForm adminModeratorForm,
            @RequestParam(defaultValue = "0") Integer offset,
            Model model) {
        adminModeratorLocationInfo(model);

        model.addAttribute("adminModeratorForm", adminModeratorForm);
        model.addAttribute("postings",
                        postingManager.begAllByModbit(
                                PostingModbit.valueOf(adminModeratorForm.getBit()),
                                offset,
                                20,
                                adminModeratorForm.isAsc()));
        return "admin-moderator";
    }

    public LocationInfo adminModeratorLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/admin/moderator")
                .withTopics("topics-admin")
                .withTopicsIndex("moderator")
                .withParent(adminController.adminLocationInfo(null))
                .withPageTitle("Модератор");
    }

    @PostMapping("/actions/postings/modbits")
    public String actionModerateMass(
            @ModelAttribute @Valid ModerateMassForm moderateMassForm,
            Errors errors,
            RedirectAttributes redirectAttributes) {
        new ControllerAction(EntryController.class, "actionModerateMass", errors)
                .transactional(txManager)
                .execute(() -> {
                    if (!requestContext.isUserModerator()) {
                        return "notModerator";
                    }
                    for (long id : moderateMassForm.getIds()) {
                        Posting posting = postingManager.beg(id);

                        if (posting == null) {
                            return "noPosting";
                        }

                        if (moderateMassForm.isSpam(id) && posting.getUser() != null) {
                            userManager.ban(posting.getUser());
                        }
                        if (moderateMassForm.isSpam(id) || moderateMassForm.isDelete(id)) {
                            postingManager.drop(posting);
                        } else {
                            moderateMassForm.toPosting(posting);
                            postingManager.save(posting);
                        }
                    }

                    return null;
                });

        if (!errors.hasErrors()) {
            return "redirect:" + requestContext.getBack();
        } else {
            redirectAttributes.addFlashAttribute("errors", errors);
            redirectAttributes.addFlashAttribute("moderateMassForm", moderateMassForm);
            return "redirect:" + requestContext.getBack();
        }
    }

}