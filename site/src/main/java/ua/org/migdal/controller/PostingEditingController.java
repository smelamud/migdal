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
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.multipart.MultipartFile;
import org.springframework.web.servlet.mvc.support.RedirectAttributes;

import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.data.Entry;
import ua.org.migdal.data.Posting;
import ua.org.migdal.data.Topic;
import ua.org.migdal.data.User;
import ua.org.migdal.form.PostingDeleteForm;
import ua.org.migdal.form.PostingForm;
import ua.org.migdal.grp.GrpDescriptor;
import ua.org.migdal.grp.GrpEditor;
import ua.org.migdal.grp.GrpEnum;
import ua.org.migdal.imageupload.ImageUploadException;
import ua.org.migdal.imageupload.ImageUploadManager;
import ua.org.migdal.manager.IdentManager;
import ua.org.migdal.manager.ImageFileManager;
import ua.org.migdal.manager.LoginManager;
import ua.org.migdal.manager.PostingManager;
import ua.org.migdal.manager.ReloginVariant;
import ua.org.migdal.manager.SpamManager;
import ua.org.migdal.manager.TopicManager;
import ua.org.migdal.manager.UserManager;
import ua.org.migdal.session.RequestContext;
import ua.org.migdal.util.Utils;

@Controller
public class PostingEditingController {

    @Inject
    private GrpEnum grpEnum;

    @Inject
    private PlatformTransactionManager txManager;

    @Inject
    private RequestContext requestContext;

    @Inject
    private IdentManager identManager;

    @Inject
    private TopicManager topicManager;

    @Inject
    private PostingManager postingManager;

    @Inject
    private UserManager userManager;

    @Inject
    private ImageFileManager imageFileManager;

    @Inject
    private SpamManager spamManager;

    @Inject
    private ImageUploadManager imageUploadManager;

    @Inject
    private LoginManager loginManager;

    private Posting createPosting(String grpName) {
        GrpDescriptor grpDescriptor = grpEnum.grp(grpName);
        Topic topic = null;
        if (!StringUtils.isEmpty(grpDescriptor.getDefaultIdent())) {
            long id = identManager.getIdByIdent(grpDescriptor.getDefaultIdent());
            topic = topicManager.beg(id);
        }
        return new Posting(grpDescriptor.getValue(), topic, topic, 0, requestContext);
    }

    private Posting openPosting(Long id) throws PageNotFoundException {
        if (id == null) {
            return null;
        }

        Posting posting = postingManager.beg(id);
        if (posting == null) {
            throw new PageNotFoundException();
        }
        return posting;
    }

    String postingAddOrEdit(Long id, String grpName, boolean full, Model model) throws PageNotFoundException {
        if (full && !requestContext.isUserModerator()) {
            throw new PageNotFoundException();
        }

        Posting posting = openPosting(id);

        if (posting == null && !grpEnum.exists(grpName)) {
            throw new PageNotFoundException();
        }

        model.addAttribute("noguests", true);
        model.addAttribute("xmlid", posting != null && full ? posting.getId() : 0);
        model.asMap().computeIfAbsent("postingForm",
                key -> new PostingForm(posting != null ? posting : createPosting(grpName), full, requestContext));
        PostingForm postingForm = (PostingForm) model.asMap().get("postingForm");
        String rootIdent = postingForm.getGrpInfo().getRootIdent();
        long rootId = full || rootIdent == null ? 0 : identManager.getIdByIdent(rootIdent);
        long grp = full ? -1 : grpEnum.grpValue(grpName);
        model.addAttribute("topicNames", topicManager.begNames(rootId, grp, false, !full));
        return "posting-edit";
    }

    @PostMapping("/actions/posting/modify")
    public String actionPostingModify(
            @RequestParam(required = false) MultipartFile imageFile,
            @ModelAttribute @Valid PostingForm postingForm,
            Errors errors,
            RedirectAttributes redirectAttributes) {
        String reloginErrorCode = loginManager.relogin(
                ReloginVariant.valueOf(postingForm.getRelogin()),
                postingForm.getGuestLogin(),
                postingForm.getLogin(),
                postingForm.getPassword(),
                postingForm.isRemember());

        if (postingForm.getUpId() <= 0) {
            postingForm.setUpId(postingForm.getParentId());
        }
        Topic upTopic = topicManager.beg(postingForm.getUpId());
        Posting upPosting = null;
        if (upTopic == null) {
            upPosting = postingManager.beg(postingForm.getUpId());
            if (upPosting != null) { // up is a Posting
                postingForm.setParentId(upPosting.getParentId());
            }
        } else { // up is a Topic
            postingForm.setUpId(postingForm.getParentId());
        }
        Topic parent = topicManager.beg(postingForm.getParentId());
        Entry up = upTopic != null ? parent : upPosting;
        Long index1 = Utils.toLong(postingForm.getIndex1());

        Posting posting;
        if (postingForm.getId() <= 0) {
            posting = new Posting(grpEnum.grpValue("NEWS"), parent, up, index1 != null ? index1 : 0, requestContext);
        } else {
            posting = postingManager.beg(postingForm.getId());
        }

        new ControllerAction(PostingController.class, "actionPostingModify", errors)
                .transactional(txManager)
                .constraint("entries_ident_key", "ident.used")
                .execute(() -> {
                    GrpEditor editor = postingForm.getGrpInfo().getFieldEditor("image");
                    if (editor != null) {
                        try {
                            String uploadedImageUuid = imageUploadManager.uploadStandard(
                                    imageFile, editor.getThumbnailStyle(), editor.getImageStyle(),
                                    editor.getThumbExactX(), editor.getThumbExactY(),
                                    editor.getThumbMaxX(), editor.getThumbMaxY(),
                                    editor.getImageExactX(), editor.getImageExactY(),
                                    editor.getImageMaxX(), editor.getImageMaxY());
                            if (!StringUtils.isEmpty(uploadedImageUuid)) {
                                postingForm.setImageUuid(uploadedImageUuid);
                            }
                        } catch (ImageUploadException e) {
                            e.setFieldName("imageFile");
                            throw e;
                        }
                    }

                    if (reloginErrorCode != null) {
                        return "relogin." + reloginErrorCode;
                    }

                    if (postingForm.getId() > 0) {
                        if (posting == null) {
                            return "noPosting";
                        }
                        if (!posting.isWritable()) {
                            return "notEditable";
                        }
                    }

                    if (!grpEnum.exists(postingForm.getGrp())) {
                        return "grp.noGrp";
                    }
                    if (postingForm.isMandatory("body") && StringUtils.isEmpty(postingForm.getBody())) {
                        return "body.NotBlank";
                    }
                    if (postingForm.isSpam(spamManager)) {
                        return "spam";
                    }
                    if (postingForm.isMandatory("lang") && StringUtils.isEmpty(postingForm.getLang())) {
                        return "lang.NotBlank";
                    }
                    if (postingForm.isMandatory("subject") && StringUtils.isEmpty(postingForm.getSubject())) {
                        return "subject.NotBlank";
                    }
                    if (postingForm.isMandatory("author") && StringUtils.isEmpty(postingForm.getAuthor())) {
                        return "author.NotBlank";
                    }
                    if (postingForm.isMandatory("source") && StringUtils.isEmpty(postingForm.getSource())) {
                        return "source.NotBlank";
                    }
                    if (postingForm.isMandatory("large_body") && StringUtils.isEmpty(postingForm.getLargeBody())) {
                        return "largeBody.NotBlank";
                    }
                    if (postingForm.isMandatory("image") && postingForm.getImage() == null) {
                        return "imageFile.NotBlank";
                    }
                    if (postingForm.isMandatory("url") && StringUtils.isEmpty(postingForm.getUrl())) {
                        return "url.NotBlank";
                    }
                    if (postingForm.isMandatory("topic") && parent == null) {
                        return "parentId.noParentId";
                    }
                    if (up == null) {
                        return "upId.noPosting";
                    }

                    String errorCode = Posting.validateHierarchy(parent, up, postingForm.getId());
                    if (errorCode != null) {
                        return errorCode;
                    }

                    if (!parent.isPostable()) {
                        return "parentId.noPost";
                    }
                    if (up.getId() != parent.getId() && !up.isAppendable()) {
                        return "upId.noAppend";
                    }

                    if (postingForm.isMandatory("ident") && StringUtils.isEmpty(postingForm.getIdent())) {
                        return "ident.NotBlank";
                    }
                    if (postingForm.isMandatory("index1") && StringUtils.isEmpty(postingForm.getIndex1())) {
                        return "index1.NotBlank";
                    }
                    if (!StringUtils.isEmpty(postingForm.getIndex1()) && index1 == null) {
                        return "index1.notNumber";
                    }
                    if (!StringUtils.isEmpty(postingForm.getPriority())
                            && Utils.toShort(postingForm.getPriority()) == null) {
                        return "priority.notNumber";
                    }
                    User person = postingForm.getPersonId() > 0 ? userManager.beg(postingForm.getPersonId()) : null;
                    if (postingForm.getPersonId() > 0 && (person == null || !person.isHasPersonal())) {
                        return "personId.noPerson";
                    }
                    /*if ($posting->getId() <= 0 && $userId <= 0) {
                        if ($captcha == '')
                            return EP_CAPTCHA_ABSENT;
                        if (!validateCaptcha($captcha))
                            return EP_CAPTCHA;
                    }*/

                    postingManager.store(
                            posting,
                            p -> postingForm.toPosting(p, up, parent, person, imageFileManager, requestContext),
                            postingForm.getId() <= 0,
                            postingForm.isTrackChanged(posting),
                            postingForm.isCatalogChanged(posting),
                            postingForm.isTopicChanged(posting));

                    return null;
                });

        if (!errors.hasErrors()) {
            return "redirect:" + requestContext.getOrigin();
        } else {
            redirectAttributes.addFlashAttribute("errors", errors);
            redirectAttributes.addFlashAttribute("postingForm", postingForm);
            return "redirect:" + requestContext.getBack();
        }
    }

    @GetMapping("/actions/posting/delete")  // FIXME leave only POST
    @PostMapping("/actions/posting/delete")
    public String actionPostingDelete(
            @ModelAttribute @Valid PostingDeleteForm postingDeleteForm,
            Errors errors,
            RedirectAttributes redirectAttributes) {
        new ControllerAction(EntryController.class, "actionPostingDelete", errors)
                .transactional(txManager)
                .execute(() -> {
                    Posting posting = postingManager.beg(postingDeleteForm.getId());
                    if (posting == null) {
                        return "noPosting";
                    }
                    if (!posting.isWritable()) {
                        return "noDelete";
                    }
                    postingManager.drop(posting);
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
