package ua.org.migdal.controller;

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
import org.springframework.web.servlet.mvc.support.RedirectAttributes;

import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.data.Topic;
import ua.org.migdal.data.util.Tree;
import ua.org.migdal.form.TopicForm;
import ua.org.migdal.manager.TopicManager;
import ua.org.migdal.session.LocationInfo;
import ua.org.migdal.session.RequestContext;

@Controller
public class TopicController {

    @Inject
    private PlatformTransactionManager txManager;

    @Inject
    private RequestContext requestContext;

    @Inject
    private TopicManager topicManager;

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
        return "topicedit";
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
        Topic topic = topicForm.getId() > 0 ? topicManager.beg(topicForm.getId()) : new Topic();
        new ControllerAction(TopicController.class, "actionTopicModify", errors)
                .transactional(txManager)
                .constraint("entries_ident_key", "ident.used")
                .execute(() -> {
                    //topicForm.toTopic(topic);
                    topicManager.save(topic);
                    return null;
                });

        if (!errors.hasErrors()) {
            return "redirect:" + requestContext.getBack();
        } else {
            redirectAttributes.addFlashAttribute("errors", errors);
            redirectAttributes.addFlashAttribute("topicForm", topicForm);
            return "redirect:/admin/topics/" + topic.getTrackPath() + "edit";
        }
    }

}