package ua.org.migdal.controller;

import javax.inject.Inject;
import javax.validation.Valid;

import org.springframework.stereotype.Controller;
import org.springframework.transaction.PlatformTransactionManager;
import org.springframework.ui.Model;
import org.springframework.util.StringUtils;
import org.springframework.validation.Errors;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.ModelAttribute;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.servlet.mvc.support.RedirectAttributes;
import org.springframework.web.util.UriComponentsBuilder;

import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.data.Entry;
import ua.org.migdal.data.EntryType;
import ua.org.migdal.data.Topic;
import ua.org.migdal.data.User;
import ua.org.migdal.form.ChmodForm;
import ua.org.migdal.form.ReorderForm;
import ua.org.migdal.manager.EntryManagerBase;
import ua.org.migdal.manager.PermManager;
import ua.org.migdal.manager.PostingManager;
import ua.org.migdal.manager.TopicManager;
import ua.org.migdal.manager.UserManager;
import ua.org.migdal.session.LocationInfo;
import ua.org.migdal.session.RequestContext;
import ua.org.migdal.util.PermUtils;

@Controller
public class EntryController {

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
    private PermManager permManager;

    @Inject
    private TopicController topicController;

    @GetMapping("/admin/topics/**/{id}/chmod")
    public String topicChmod(@PathVariable long id, Model model) throws PageNotFoundException {
        Topic topic = topicManager.beg(id);
        if (topic == null) {
            throw new PageNotFoundException();
        }

        topicChmodLocationInfo(topic, model);

        model.asMap().computeIfAbsent("chmodForm", key -> new ChmodForm(topic));
        return "chmod";
    }

    public LocationInfo topicChmodLocationInfo(Topic topic, Model model) {
        return new LocationInfo(model)
                .withUri("/admin/topics/" + topic.getTrackPath() + "chmod")
                .withParent(topicController.adminTopicsLocationInfo(null))
                .withPageTitle("Изменение прав на тему");
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
                // TODO Fetch posting and maybe do this via manager
                manager = postingManager;
                entry = null;
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
            return "redirect:" + requestContext.getBack();
        } else {
            redirectAttributes.addFlashAttribute("errors", errors);
            redirectAttributes.addFlashAttribute("chmodForm", chmodForm);
            String location;
            switch (entryType) {
                case TOPIC:
                    location = "redirect:/admin/topics/" + (entry != null ? entry.getTrackPath() : "") + "chmod";
                    break;
                case POSTING:
                    location = "redirect:/admin/postings"; // FIXME
                    break;
                default:
                    location = "redirect:/admin/topics";
                    break;
            }
            return UriComponentsBuilder.fromUriString(location)
                    .queryParam("back", requestContext.getBack())
                    .toUriString();
        }
    }

    @GetMapping("/admin/topics/**/{id}/reorder")
    public String topicReorder(@PathVariable long id, Model model) throws PageNotFoundException {
        Topic topic = topicManager.beg(id);
        if (topic == null) {
            throw new PageNotFoundException();
        }

        topicReorderLocationInfo(topic, model);

        model.addAttribute("entries", topicManager.begAll(id, false, "index0"));
        model.asMap().computeIfAbsent("reorderForm", key -> new ReorderForm(EntryType.TOPIC));
        return "reorder";
    }

    public LocationInfo topicReorderLocationInfo(Topic topic, Model model) {
        return new LocationInfo(model)
                .withUri("/admin/topics/" + topic.getTrackPath() + "reorder")
                .withParent(topicController.adminTopicsLocationInfo(null))
                .withPageTitle("Расстановка подтем");
    }

}