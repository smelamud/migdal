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
import ua.org.migdal.data.Comment;
import ua.org.migdal.data.Posting;
import ua.org.migdal.form.CommentDeleteForm;
import ua.org.migdal.form.CommentForm;
import ua.org.migdal.location.LocationInfo;
import ua.org.migdal.manager.CommentManager;
import ua.org.migdal.manager.LoginManager;
import ua.org.migdal.manager.PostingManager;
import ua.org.migdal.manager.ReloginVariant;
import ua.org.migdal.manager.SpamManager;
import ua.org.migdal.session.RequestContext;
import ua.org.migdal.util.Captcha;

@Controller
public class CommentController {

    @Inject
    private PlatformTransactionManager txManager;

    @Inject
    private RequestContext requestContext;

    @Inject
    private CommentManager commentManager;

    @Inject
    private PostingManager postingManager;

    @Inject
    private LoginManager loginManager;

    @Inject
    private SpamManager spamManager;

    @Inject
    private Captcha captcha;

    @Inject
    private IndexController indexController;

    @GetMapping("/add-comment/{id}")
    public String commentAdd(@PathVariable long id, Model model) throws PageNotFoundException {
        Posting parent = postingManager.beg(id);
        if (parent == null) {
            throw new PageNotFoundException();
        }

        commentAddLocationInfo(parent, model);

        model.addAttribute("captchaOnPage", !requestContext.isLogged());
        model.addAttribute("posting", parent);
        model.addAttribute("xmlid", 0);
        model.asMap().computeIfAbsent("commentForm",
                key -> new CommentForm(parent, requestContext));
        return "comment-edit";
    }

    public LocationInfo commentAddLocationInfo(Posting parent, Model model) {
        return new LocationInfo(model)
                .withUri("/add-comment/" + parent.getId())
                .withParent(indexController.indexLocationInfo(null))
                .withPageTitle("Добавление комментария");
    }

    @GetMapping("/edit-comment/{id}")
    public String commentEdit(@PathVariable long id, Model model) throws PageNotFoundException {
        Comment comment = commentManager.beg(id);
        if (comment == null) {
            throw new PageNotFoundException();
        }

        commentEditLocationInfo(comment, model);

        model.addAttribute("posting", comment.getParent());
        model.addAttribute("xmlid", requestContext.isUserModerator() ? comment.getId() : 0);
        model.asMap().computeIfAbsent("commentForm", key -> new CommentForm(comment, requestContext));
        return "comment-edit";
    }

    public LocationInfo commentEditLocationInfo(Comment comment, Model model) {
        return new LocationInfo(model)
                .withUri("/edit-comment/" + comment.getId())
                .withParent(indexController.indexLocationInfo(null))
                .withPageTitle("Редактирование комментария");
    }

    @PostMapping("/actions/comment/modify")
    public String actionCommentModify(
            @ModelAttribute @Valid CommentForm commentForm,
            Errors errors,
            RedirectAttributes redirectAttributes) {
        Posting parent = postingManager.beg(commentForm.getParentId());

        String reloginErrorCode = loginManager.relogin(
                ReloginVariant.valueOf(commentForm.getRelogin()),
                commentForm.getGuestLogin(),
                commentForm.getLogin(),
                commentForm.getPassword(),
                commentForm.isRemember(),
                parent == null || !parent.isGuestPostable());

        Comment comment;
        if (commentForm.getId() <= 0) {
            comment = new Comment(parent, requestContext);
        } else {
            comment = commentManager.beg(commentForm.getId());
        }

        new ControllerAction(CommentController.class, "actionCommentModify", errors)
                .transactional(txManager)
                .execute(() -> {
                    if (reloginErrorCode != null) {
                        return "relogin." + reloginErrorCode;
                    }

                    if (commentForm.getId() > 0) {
                        if (comment == null) {
                            return "noComment";
                        }
                        if (!comment.isWritable()) {
                            return "notEditable";
                        }
                    }

                    if (parent == null) {
                        return "parentId.noParentId";
                    }
                    String errorCode = Comment.validateHierarchy(parent, parent, commentForm.getId());
                    if (errorCode != null) {
                        return errorCode;
                    }
                    if (!parent.isPostable()) {
                        return "parentId.noPost";
                    }
                    if (commentForm.isSpam(spamManager)) {
                        return "spam";
                    }
                    if (!requestContext.isLogged() && !captcha.valid(commentForm.getCaptchaResponse())) {
                        return "wrongCaptcha";
                    }

                    commentManager.store(
                            comment,
                            f -> commentForm.toComment(f, parent, requestContext),
                            commentForm.getId() <= 0,
                            commentForm.isTrackChanged(comment),
                            commentForm.isCatalogChanged(comment));

                    return null;
                });

        if (!errors.hasErrors()) {
            return UriComponentsBuilder.fromUriString("redirect:" + requestContext.getOrigin())
                    .replaceQueryParam("tid", comment.getId())
                    .fragment("t" + comment.getId())
                    .toUriString();
        } else {
            redirectAttributes.addFlashAttribute("errors", errors);
            redirectAttributes.addFlashAttribute("commentForm", commentForm);
            String location;
            if (commentForm.getId() <= 0) {
                location = "redirect:/add-comment/" + (parent != null ? parent.getId() : "");
            } else {
                location = "redirect:/edit-comment/" + commentForm.getId();
            }
            return UriComponentsBuilder.fromUriString(location)
                    .queryParam("back", requestContext.getOrigin())
                    .toUriString();
        }
    }

    @GetMapping("/actions/comment/delete")  // FIXME leave only POST
    @PostMapping("/actions/comment/delete")
    public String actionCommentDelete(
            @ModelAttribute @Valid CommentDeleteForm commentDeleteForm,
            Errors errors,
            RedirectAttributes redirectAttributes) {
        new ControllerAction(EntryController.class, "actionCommentDelete", errors)
                .transactional(txManager)
                .execute(() -> {
                    Comment comment = commentManager.beg(commentDeleteForm.getId());
                    if (comment == null) {
                        return "noComment";
                    }
                    if (!comment.isWritable()) {
                        return "noDelete";
                    }
                    commentManager.drop(comment);
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
