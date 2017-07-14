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
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.servlet.mvc.support.RedirectAttributes;

import org.springframework.web.util.UriComponentsBuilder;
import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.data.EntryType;
import ua.org.migdal.data.Topic;
import ua.org.migdal.data.User;
import ua.org.migdal.data.util.Tree;
import ua.org.migdal.form.TopicDeleteForm;
import ua.org.migdal.form.TopicForm;
import ua.org.migdal.manager.CatalogManager;
import ua.org.migdal.manager.PostingManager;
import ua.org.migdal.manager.TopicManager;
import ua.org.migdal.manager.TrackManager;
import ua.org.migdal.manager.UserManager;
import ua.org.migdal.session.LocationInfo;
import ua.org.migdal.session.RequestContext;
import ua.org.migdal.util.CatalogUtils;
import ua.org.migdal.util.PermUtils;
import ua.org.migdal.util.TrackUtils;

@Controller
public class TopicController {

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
    private TrackManager trackManager;

    @Inject
    private CatalogManager catalogManager;

    @Inject
    private IndexController indexController;

    @GetMapping("/admin/topics")
    public String adminTopicsRoot(Model model) throws PageNotFoundException {
        return adminTopics(null, model);
    }

    @GetMapping("/admin/topics/**/{upId}")
    public String adminTopicsSubtree(@PathVariable long upId, Model model) throws PageNotFoundException {
        return adminTopics(upId, model);
    }

    private String adminTopics(Long upId, Model model) throws PageNotFoundException {
        adminTopicsLocationInfo(model);

        Tree<Topic> topicTree;
        if (upId != null) {
            Topic up = topicManager.beg(upId);
            if (up == null) {
                throw new PageNotFoundException();
            }
            model.addAttribute("up", up);
            model.addAttribute("ancestors", topicManager.begAncestors(upId));
            topicTree = new Tree<>(upId, topicManager.begAll(upId, true));
        } else {
            model.addAttribute("up", null);
            model.addAttribute("ancestors", null);
            topicTree = new Tree<>(topicManager.begAll(0, true));
        }
        topicTree.sort((topic1, topic2) -> topic1.getSubject().compareToIgnoreCase(topic2.getSubject()));
        model.addAttribute("topicTree", topicTree);
        return "admin-topics";
    }

    public LocationInfo adminTopicsLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/admin/topics")
                .withTopics("topics-admin")
                .withTopicsIndex("admin-topics")
                .withParent(indexController.indexLocationInfo(null))
                .withPageTitle("Темы");
    }

    @GetMapping("/admin/topics/add")
    public String topicAddTop(Model model) throws PageNotFoundException {
        return topicAdd(0, model);
    }

    @GetMapping("/admin/topics/**/{id}/add")
    public String topicAddNotTop(@PathVariable long id, Model model) throws PageNotFoundException {
        return topicAdd(id, model);
    }

    private String topicAdd(long id, Model model) throws PageNotFoundException {
        Topic up = topicManager.beg(id);
        if (up == null) {
            throw new PageNotFoundException();
        }

        topicAddLocationInfo(up, model);

        model.addAttribute("xmlid", 0);
        model.addAttribute("topicNames", topicManager.begNames(0, -1, true, false));
        model.asMap().putIfAbsent("topicForm", new TopicForm(new Topic(up, requestContext)));
        return "topic-edit";
    }

    public LocationInfo topicAddLocationInfo(Topic up, Model model) {
        return new LocationInfo(model)
                .withUri("/admin/topics/" + up.getTrackPath() + "add")
                .withParent(adminTopicsLocationInfo(null))
                .withPageTitle("Добавление темы");
    }

    @GetMapping("/admin/topics/**/{id}/edit")
    public String topicEdit(@PathVariable long id, Model model) throws PageNotFoundException {
        Topic topic = topicManager.beg(id);
        if (topic == null) {
            throw new PageNotFoundException();
        }

        topicEditLocationInfo(topic, model);

        model.addAttribute("xmlid", requestContext.isUserModerator() ? topic.getId() : 0);
        model.addAttribute("topicNames", topicManager.begNames(0, -1, true, false));
        model.asMap().putIfAbsent("topicForm", new TopicForm(topic));
        return "topic-edit";
    }

    public LocationInfo topicEditLocationInfo(Topic topic, Model model) {
        return new LocationInfo(model)
                .withUri("/admin/topics/" + topic.getTrackPath() + "edit")
                .withParent(adminTopicsLocationInfo(null))
                .withPageTitle("Редактирование темы");
    }

    @PostMapping("/actions/topic/modify")
    public String actionTopicModify(
            @ModelAttribute @Valid TopicForm topicForm,
            Errors errors,
            RedirectAttributes redirectAttributes) {
        if (topicForm.getUpId() < 0) {
            topicForm.setUpId(0);
        }
        Topic up = topicManager.beg(topicForm.getUpId());

        Topic topic;
        if (topicForm.getId() <= 0) {
            topic = new Topic(up, requestContext);
        } else {
            topic = topicManager.beg(topicForm.getId());
        }

        new ControllerAction(TopicController.class, "actionTopicModify", errors)
                .transactional(txManager)
                .constraint("entries_ident_key", "ident.used")
                .execute(() -> {
                    if (topicForm.getId() > 0) {
                        if (topic == null) {
                            return "noTopic";
                        }
                        if (!topic.isWritable()) {
                            return "notEditable";
                        }
                    }

                    User user;
                    if (StringUtils.isEmpty(topicForm.getUserName())) {
                        user = userManager.get(requestContext.getUserId());
                    } else {
                        user = userManager.getByLogin(topicForm.getUserName());
                    }
                    if (user == null) {
                        return "userName.noUser";
                    }
                    User group;
                    if (StringUtils.isEmpty(topicForm.getGroupName())) {
                        group = userManager.get(requestContext.getUserId());
                    } else {
                        group = userManager.getByLogin(topicForm.getGroupName());
                    }
                    if (group == null) {
                        return "groupName.noGroup";
                    }
                    long perms = PermUtils.parse(topicForm.getPermString());
                    if (perms < 0) {
                        return "permString.invalid";
                    }

                    if (topicForm.getUpId() > 0 && up == null) {
                        return "upId.noUp";
                    }
                    String errorCode = Topic.validateHierarchy(null, up, topicForm.getId());
                    if (errorCode != null) {
                        return errorCode;
                    }
                    if (!up.isAppendable()) {
                        return "upId.noAppend";
                    }

                    String oldTrack = topic.getTrack();
                    boolean trackChanged = topicForm.isTrackChanged(topic);
                    boolean catalogChanged = topicForm.isCatalogChanged(topic);

                    topicForm.toTopic(topic, up, user, group, requestContext);
                    topicManager.saveAndFlush(topic); // We need to have the record in DB and to know ID after this point

                    String newTrack = TrackUtils.track(topic.getId(), up.getTrack());
                    if (topicForm.getId() <= 0) {
                        trackManager.setTrackById(topic.getId(), newTrack);
                        String newCatalog = CatalogUtils.catalog(EntryType.TOPIC, topic.getId(), topic.getIdent(),
                                                                 topic.getModbits(), up.getCatalog());
                        catalogManager.setCatalogById(topic.getId(), newCatalog);
                    }
                    if (trackChanged) {
                        trackManager.replaceTracks(oldTrack, newTrack);
                    }
                    if (catalogChanged) {
                        catalogManager.updateCatalogs(newTrack);
                    }

                    return null;
                });

        if (!errors.hasErrors()) {
            return "redirect:" + requestContext.getBack();
        } else {
            redirectAttributes.addFlashAttribute("errors", errors);
            redirectAttributes.addFlashAttribute("topicForm", topicForm);
            String location;
            if (topicForm.getId() <= 0) {
                location = "redirect:/admin/topics/" + up.getTrackPath() + "add";
            } else {
                location = "redirect:/admin/topics/" + topic.getTrackPath() + "edit";
            }
            return UriComponentsBuilder.fromUriString(location)
                    .queryParam("back", requestContext.getBack())
                    .toUriString();
        }
    }

    @GetMapping("/admin/topics/**/{id}/delete")
    public String topicDelete(@PathVariable long id, Model model) throws PageNotFoundException {
        Topic topic = topicManager.beg(id);
        if (topic == null) {
            throw new PageNotFoundException();
        }

        int subtopicsCount = topicManager.getSubtopicsCount(topic.getId());
        int postsCount = postingManager.getPostingsCount(topic.getId());

        if (subtopicsCount == 0 && postsCount == 0) {
            return UriComponentsBuilder.fromUriString("redirect:/actions/topic/delete")
                    .queryParam("id", topic.getId())
                    .queryParam("back", requestContext.getBack())
                    .toUriString();
        }

        topicDeleteLocationInfo(topic, model);

        model.addAttribute("topic", topic);
        model.addAttribute("subtopics", topicManager.getSubtopicsCount(topic.getId()));
        model.addAttribute("posts", postingManager.getPostingsCount(topic.getId()));
        model.addAttribute("topicNames", topicManager.begNames(0, -1, true, true));
        model.asMap().putIfAbsent("topicDeleteForm", new TopicDeleteForm(topic.getId()));
        return "topic-delete";
    }

    public LocationInfo topicDeleteLocationInfo(Topic topic, Model model) {
        return new LocationInfo(model)
                .withUri("/admin/topics/" + topic.getTrackPath() + "delete")
                .withParent(adminTopicsLocationInfo(null))
                .withPageTitle("Удаление темы");
    }

    // FIXME need to allow only POST requests
    @RequestMapping("/actions/topic/delete")
    public String actionTopicDelete(
            @ModelAttribute @Valid TopicDeleteForm topicDeleteForm,
            Errors errors,
            RedirectAttributes redirectAttributes) {
        Topic topic = topicManager.beg(topicDeleteForm.getId());

        new ControllerAction(TopicController.class, "actionTopicDelete", errors)
                .transactional(txManager)
                .execute(() -> {
                    if (topicDeleteForm.getId() <= 0) {
                        return "absent";
                    }
                    if (topic.getId() <= 0) {
                        return "noTopic";
                    }
                    if (!topic.isWritable()) {
                        return "noDelete";
                    }

                    Topic destTopic = null;
                    boolean hasSubtopics = topicManager.getSubtopicsCount(topic.getId()) > 0;
                    boolean hasPostings = postingManager.getPostingsCount(topic.getId()) > 0;

                    if (hasSubtopics || hasPostings) {
                        if (topicDeleteForm.getDestId() <= 0 || topicDeleteForm.getDestId() == topicDeleteForm.getId()) {
                            return "destId.absent";
                        }
                        destTopic = topicManager.beg(topicDeleteForm.getDestId());
                        if (hasSubtopics && !topic.isAppendable()) {
                            return "dest.noAppend";
                        }
                        if (hasPostings && !topic.isPostable()) {
                            return "destId.noPost";
                        }
                    }
                    return null;
                });

        if (!errors.hasErrors()) {
            return "redirect:" + requestContext.getBack();
        } else {
            redirectAttributes.addFlashAttribute("errors", errors);
            redirectAttributes.addFlashAttribute("topicDeleteForm", topicDeleteForm);
            return UriComponentsBuilder.fromUriString("redirect:/admin/topics/" + topic.getTrackPath() + "delete")
                    .queryParam("back", requestContext.getBack())
                    .toUriString();
        }
    }

}