package ua.org.migdal.controller;

import java.util.HashSet;
import java.util.Set;

import javax.inject.Inject;
import javax.validation.Valid;

import org.springframework.stereotype.Controller;
import org.springframework.transaction.PlatformTransactionManager;
import org.springframework.ui.Model;
import org.springframework.util.StringUtils;
import org.springframework.validation.Errors;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.ModelAttribute;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.servlet.mvc.support.RedirectAttributes;

import ua.org.migdal.data.Entry;
import ua.org.migdal.data.EntryType;
import ua.org.migdal.data.Posting;
import ua.org.migdal.data.Topic;
import ua.org.migdal.data.User;
import ua.org.migdal.form.ChmodForm;
import ua.org.migdal.form.ModerateForm;
import ua.org.migdal.form.RenewForm;
import ua.org.migdal.form.ReorderForm;
import ua.org.migdal.manager.EntryManager;
import ua.org.migdal.manager.EntryManagerBase;
import ua.org.migdal.manager.PermManager;
import ua.org.migdal.manager.PostingManager;
import ua.org.migdal.manager.TopicManager;
import ua.org.migdal.manager.UserManager;
import ua.org.migdal.session.RequestContext;
import ua.org.migdal.util.PermUtils;

@Controller
public class EntryController {

    @Inject
    private PlatformTransactionManager txManager;

    @Inject
    private RequestContext requestContext;

    @Inject
    private EntryManager entryManager;

    @Inject
    private TopicManager topicManager;

    @Inject
    private PostingManager postingManager;

    @Inject
    private UserManager userManager;

    @Inject
    private PermManager permManager;

    public String entryChmod(Topic topic, Model model) {
        model.asMap().computeIfAbsent("chmodForm", key -> new ChmodForm(topic));
        return "chmod";
    }

    public String entryChmod(Posting posting, Model model) {
        model.asMap().computeIfAbsent("chmodForm", key -> new ChmodForm(posting));
        return "chmod";
    }

    @PostMapping("/actions/entry/chmod")
    public String actionChmod(
            @ModelAttribute @Valid ChmodForm chmodForm,
            Errors errors,
            RedirectAttributes redirectAttributes) {
        EntryType entryType = EntryType.valueOf(chmodForm.getEntryType());
        Entry entry;
        boolean moderator;
        EntryManagerBase manager;

        switch (entryType) {
            case TOPIC:
                manager = topicManager;
                entry = topicManager.beg(chmodForm.getId());
                moderator = requestContext.isUserAdminTopics();
                break;
            case POSTING:
                manager = postingManager;
                entry = postingManager.beg(chmodForm.getId());
                moderator = requestContext.isUserModerator();
                break;
            default:
                manager = null;
                entry = null;
                moderator = false;
                break;
        }

        new ControllerAction(EntryController.class, "actionChmod", errors)
                .transactional(txManager)
                .execute(() -> {
                    if (entry == null) {
                        return "noEntry";
                    }
                    if (!entry.isWritable() || chmodForm.isRecursive() && !moderator) {
                        return "noChmod";
                    }

                    User user = null;
                    if (!StringUtils.isEmpty(chmodForm.getUserName())) {
                        user = userManager.getByLogin(chmodForm.getUserName());
                        if (user == null) {
                            return "userName.noUser";
                        }
                    }
                    User group = null;
                    if (!StringUtils.isEmpty(chmodForm.getGroupName())) {
                        group = userManager.getByLogin(chmodForm.getGroupName());
                        if (group == null) {
                            return "groupName.noGroup";
                        }
                    }

                    if (!chmodForm.isRecursive()) {
                        if (user == null) {
                            return "userName.NotBlank";
                        }
                        if (group == null) {
                            return "groupName.NotBlank";
                        }
                        long perms = PermUtils.parse(chmodForm.getPermString());
                        if (perms < 0) {
                            return "permString.invalid";
                        }
                        chmodForm.toEntry(entry, user, group);
                        manager.save(entry);
                    } else {
                        if (user != null) {
                            permManager.updateUserRecursive(entryType, entry.getTrack(), user);
                        }
                        if (group != null) {
                            permManager.updateGroupRecursive(entryType, entry.getTrack(), group);
                        }
                        if (!StringUtils.isEmpty(chmodForm.getPermString())) {
                            permManager.updatePermsRecursive(entryType, entry.getTrack(), chmodForm.getPermString());
                        }
                    }

                    return null;
                });

        if (!errors.hasErrors()) {
            return "redirect:" + requestContext.getOrigin();
        } else {
            redirectAttributes.addFlashAttribute("errors", errors);
            redirectAttributes.addFlashAttribute("chmodForm", chmodForm);
            return "redirect:" + requestContext.getBack();
        }
    }

    @GetMapping("/actions/entry/moderate")  // FIXME leave only POST
    @PostMapping("/actions/entry/moderate")
    public String actionModerate(
            @ModelAttribute @Valid ModerateForm moderateForm,
            Errors errors,
            RedirectAttributes redirectAttributes) {
        new ControllerAction(EntryController.class, "actionModerate", errors)
                .transactional(txManager)
                .execute(() -> {
                    if (!requestContext.isUserModerator()) {
                        return "notModerator";
                    }
                    if (!entryManager.exists(moderateForm.getId())) {
                        return "noEntry";
                    }
                    entryManager.updateDisabledById(moderateForm.getId(), moderateForm.isHide());
                    // TODO for Forums update answers info in the parent Posting
                    return null;
                });

        if (!errors.hasErrors()) {
            return "redirect:" + requestContext.getBack();
        } else {
            redirectAttributes.addFlashAttribute("errors", errors);
            return "redirect:" + requestContext.getBack();
        }
    }

    @GetMapping("/actions/entry/renew")  // FIXME leave only POST
    @PostMapping("/actions/entry/renew")
    public String actionRenew(
            @ModelAttribute @Valid RenewForm renewForm,
            Errors errors,
            RedirectAttributes redirectAttributes) {
        new ControllerAction(EntryController.class, "actionRenew", errors)
                .transactional(txManager)
                .execute(() -> {
                    if (!requestContext.isUserModerator()) {
                        return "notModerator";
                    }
                    if (!entryManager.exists(renewForm.getId())) {
                        return "noEntry";
                    }
                    entryManager.renewById(renewForm.getId());
                    // TODO for Forums update answers info in the parent Posting
                    return null;
                });

        if (!errors.hasErrors()) {
            return "redirect:" + requestContext.getBack();
        } else {
            redirectAttributes.addFlashAttribute("errors", errors);
            return "redirect:" + requestContext.getBack();
        }
    }

    public <T extends Entry> String entryReorder(Iterable<T> entries, Model model) {
        model.asMap().computeIfAbsent("reorderForm", key -> new ReorderForm(EntryType.TOPIC));
        ((ReorderForm) model.asMap().get("reorderForm")).setEntries(entries);
        return "reorder";
    }

    @PostMapping("/actions/entry/reorder")
    public String actionReorder(
            @ModelAttribute @Valid ReorderForm reorderForm,
            Errors errors,
            RedirectAttributes redirectAttributes) {
        EntryType entryType = EntryType.valueOf(reorderForm.getEntryType());
        EntryManagerBase manager;

        switch (entryType) {
            case TOPIC:
                manager = topicManager;
                break;
            case POSTING:
                // TODO Fetch posting and maybe do this via manager
                manager = postingManager;
                break;
            default:
                manager = null;
                break;
        }

        new ControllerAction(EntryController.class, "actionReorder", errors)
                .transactional(txManager)
                .execute(() -> {
                    if (manager == null) {
                        return "unknownEntryType";
                    }

                    Set<Long> processedIds = new HashSet<>();
                    int n = 1;
                    for (long id : reorderForm.getIds()) {
                        if (processedIds.contains(id)) {
                            return "duplicate";
                        }
                        processedIds.add(id);

                        Entry entry = manager.beg(id);
                        if (!entry.isWritable()) {
                            return "noWrite";
                        }
                        entry.setIndex0(n++);
                        manager.save(entry);
                    }

                    return null;
                });

        if (!errors.hasErrors()) {
            return "redirect:" + requestContext.getOrigin();
        } else {
            redirectAttributes.addFlashAttribute("errors", errors);
            redirectAttributes.addFlashAttribute("reorderForm", reorderForm);
            return "redirect:" + requestContext.getBack();
        }
    }

}