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
import org.springframework.web.util.UriComponentsBuilder;

import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.data.Forum;
import ua.org.migdal.data.Posting;
import ua.org.migdal.form.ForumDeleteForm;
import ua.org.migdal.form.ForumForm;
import ua.org.migdal.location.LocationInfo;
import ua.org.migdal.manager.ForumManager;
import ua.org.migdal.manager.LoginManager;
import ua.org.migdal.manager.PostingManager;
import ua.org.migdal.manager.ReloginVariant;
import ua.org.migdal.manager.SpamManager;
import ua.org.migdal.session.RequestContext;

@Controller
public class ForumController {

    @Inject
    private PlatformTransactionManager txManager;

    @Inject
    private RequestContext requestContext;

    @Inject
    private ForumManager forumManager;

    @Inject
    private PostingManager postingManager;

    @Inject
    private LoginManager loginManager;

    @Inject
    private SpamManager spamManager;

    @Inject
    private IndexController indexController;

    @GetMapping("/add-forum/{id}")
    public String forumAdd(@PathVariable long id, Model model) throws PageNotFoundException {
        Posting parent = postingManager.beg(id);
        if (parent == null) {
            throw new PageNotFoundException();
        }

        forumAddLocationInfo(parent, model);

        model.addAttribute("posting", parent);
        model.addAttribute("xmlid", 0);
        model.asMap().computeIfAbsent("forumForm",
                key -> new ForumForm(parent, requestContext));
        return "forum-edit";
    }

    public LocationInfo forumAddLocationInfo(Posting parent, Model model) {
        return new LocationInfo(model)
                .withUri("/add-forum/" + parent.getId())
                .withParent(indexController.indexLocationInfo(null))
                .withPageTitle("Добавление комментария");
    }

    @GetMapping("/edit-forum/{id}")
    public String forumEdit(@PathVariable long id, Model model) throws PageNotFoundException {
        Forum forum = forumManager.beg(id);
        if (forum == null) {
            throw new PageNotFoundException();
        }

        forumEditLocationInfo(forum, model);

        model.addAttribute("posting", forum.getParent());
        model.addAttribute("xmlid", requestContext.isUserModerator() ? forum.getId() : 0);
        model.asMap().computeIfAbsent("forumForm", key -> new ForumForm(forum, requestContext));
        return "forum-edit";
    }

    public LocationInfo forumEditLocationInfo(Forum forum, Model model) {
        return new LocationInfo(model)
                .withUri("/edit-forum/" + forum.getId())
                .withParent(indexController.indexLocationInfo(null))
                .withPageTitle("Редактирование комментария");
    }

    @PostMapping("/actions/forum/modify")
    public String actionForumModify(
            @ModelAttribute @Valid ForumForm forumForm,
            Errors errors,
            RedirectAttributes redirectAttributes) {
        Posting parent = postingManager.beg(forumForm.getParentId());

        String reloginErrorCode = loginManager.relogin(
                ReloginVariant.valueOf(forumForm.getRelogin()),
                forumForm.getGuestLogin(),
                forumForm.getLogin(),
                forumForm.getPassword(),
                forumForm.isRemember(),
                parent == null || !parent.isGuestPostable());

        Forum forum;
        if (forumForm.getId() <= 0) {
            forum = new Forum(parent, requestContext);
        } else {
            forum = forumManager.beg(forumForm.getId());
        }

        new ControllerAction(ForumController.class, "actionForumModify", errors)
                .transactional(txManager)
                .execute(() -> {
                    if (reloginErrorCode != null) {
                        return "relogin." + reloginErrorCode;
                    }

                    if (forumForm.getId() > 0) {
                        if (forum == null) {
                            return "noForum";
                        }
                        if (!forum.isWritable()) {
                            return "notEditable";
                        }
                    }

                    if (parent == null) {
                        return "parentId.noParentId";
                    }
                    String errorCode = Forum.validateHierarchy(parent, parent, forumForm.getId());
                    if (errorCode != null) {
                        return errorCode;
                    }
                    if (!parent.isPostable()) {
                        return "parentId.noPost";
                    }
                    if (forumForm.isSpam(spamManager)) {
                        return "spam";
                    }
                    /* TODO
                    if ($posting->getId() <= 0 && $userId <= 0) {
                        if ($captcha == '')
                            return EP_CAPTCHA_ABSENT;
                        if (!validateCaptcha($captcha))
                            return EP_CAPTCHA;
                    }*/

                    forumManager.store(
                            forum,
                            f -> forumForm.toForum(f, parent, requestContext),
                            forumForm.getId() <= 0,
                            forumForm.isTrackChanged(forum),
                            forumForm.isCatalogChanged(forum));

                    return null;
                });

        if (!errors.hasErrors()) {
            return "redirect:" + requestContext.getOrigin();
        } else {
            redirectAttributes.addFlashAttribute("errors", errors);
            redirectAttributes.addFlashAttribute("forumForm", forumForm);
            String location;
            if (forumForm.getId() <= 0) {
                location = "redirect:/add-forum/" + parent.getId();
            } else {
                location = "redirect:/edit-forum/" + forumForm.getId();
            }
            return UriComponentsBuilder.fromUriString(location)
                    .queryParam("back", requestContext.getOrigin())
                    .toUriString();
        }
    }

    @GetMapping("/actions/forum/delete")  // FIXME leave only POST
    @PostMapping("/actions/forum/delete")
    public String actionForumDelete(
            @ModelAttribute @Valid ForumDeleteForm forumDeleteForm,
            Errors errors,
            RedirectAttributes redirectAttributes) {
        new ControllerAction(EntryController.class, "actionForumDelete", errors)
                .transactional(txManager)
                .execute(() -> {
                    Forum forum = forumManager.beg(forumDeleteForm.getId());
                    if (forum == null) {
                        return "noForum";
                    }
                    if (!forum.isWritable()) {
                        return "noDelete";
                    }
                    forumManager.drop(forum);
                    return null;
                });

        if (!errors.hasErrors()) {
            return "redirect:" + requestContext.getBack();
        } else {
            redirectAttributes.addFlashAttribute("errors", errors);
            return "redirect:" + requestContext.getBack();
        }
    }

}
