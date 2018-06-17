package ua.org.migdal.controller;

import javax.inject.Inject;
import javax.validation.Valid;

import org.springframework.stereotype.Controller;
import org.springframework.transaction.PlatformTransactionManager;
import org.springframework.validation.Errors;
import org.springframework.web.bind.annotation.ModelAttribute;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.servlet.mvc.support.RedirectAttributes;

import ua.org.migdal.data.Forum;
import ua.org.migdal.data.Posting;
import ua.org.migdal.form.ForumForm;
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

    @PostMapping("/actions/forum/modify")
    public String actionForumModify(
            @ModelAttribute @Valid ForumForm forumForm,
            Errors errors,
            RedirectAttributes redirectAttributes) {
        String reloginErrorCode = loginManager.relogin(
                ReloginVariant.valueOf(forumForm.getRelogin()),
                forumForm.getGuestLogin(),
                forumForm.getLogin(),
                forumForm.getPassword(),
                forumForm.isRemember());

        Posting parent = postingManager.beg(forumForm.getParentId());

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
            return "redirect:" + requestContext.getBack();
            /*String location;
            if (forumForm.getId() <= 0) {
                location = "redirect:/admin/topics/" + parent.getTrackPath() + "add";
            } else {
                location = "redirect:/admin/topics/" + forum.getTrackPath() + "edit";
            }
            return UriComponentsBuilder.fromUriString(location)
                    .queryParam("back", requestContext.getOrigin())
                    .toUriString();*/
        }
    }

}
