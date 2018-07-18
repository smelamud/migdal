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
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.multipart.MultipartFile;
import org.springframework.web.servlet.mvc.support.RedirectAttributes;

import ua.org.migdal.Config;
import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.data.Posting;
import ua.org.migdal.form.InnerImageForm;
import ua.org.migdal.grp.ImageTransformFlag;
import ua.org.migdal.grp.ThumbnailTransformFlag;
import ua.org.migdal.imageupload.ImageUploadException;
import ua.org.migdal.imageupload.ImageUploadManager;
import ua.org.migdal.location.LocationInfo;
import ua.org.migdal.manager.PostingManager;
import ua.org.migdal.session.RequestContext;
import ua.org.migdal.util.Utils;

@Controller
public class InnerImageController {

    @Inject
    private Config config;

    @Inject
    private PlatformTransactionManager txManager;

    @Inject
    private RequestContext requestContext;

    @Inject
    private PostingManager postingManager;

    @Inject
    private ImageUploadManager imageUploadManager;

    @Inject
    private PostingViewController postingViewController;

    @GetMapping("/insert-inner-image/{postingId}")
    public String innerImageInsert(
            @PathVariable long postingId,
            @RequestParam int paragraph,
            @RequestParam(defaultValue = "0") int x,
            @RequestParam(defaultValue = "0") int y,
            Model model) throws PageNotFoundException {

        Posting posting = postingManager.beg(postingId);
        if (posting == null) {
            throw new PageNotFoundException();
        }

        innerImageInsertLocationInfo(posting, model);

        model.addAttribute("posting", posting);
        model.asMap().computeIfAbsent("innerImageForm",
                key -> new InnerImageForm(postingId, paragraph, x, y));
        return "inner-image-insert";
    }

    public LocationInfo innerImageInsertLocationInfo(Posting posting, Model model) {
        return new LocationInfo(model)
                .withUri("/insert-inner-image/" + posting.getId())
                .withParent(postingViewController.postingViewLocationInfo(posting, model))
                .withPageTitle(String.format("Вставка картинки в статью \"%s\"", posting.getSubject()));
    }

    @PostMapping("/actions/inner-image/modify")
    public String actionInnerImageModify(
            @RequestParam(required = false) MultipartFile imageFile,
            @ModelAttribute @Valid InnerImageForm innerImageForm,
            Errors errors,
            RedirectAttributes redirectAttributes) {
        Posting posting = postingManager.beg(innerImageForm.getPostingId());

        new ControllerAction(InnerImageController.class, "actionInnerImageModify", errors)
                .transactional(txManager)
                .execute(() -> {
                    ThumbnailTransformFlag thumbFlag = innerImageForm.isThumbnail()
                            ? ThumbnailTransformFlag.AUTO : ThumbnailTransformFlag.NONE;
                    ImageTransformFlag imageFlag = !innerImageForm.isNoResize()
                            ? ImageTransformFlag.RESIZE : ImageTransformFlag.MANUAL;
                    short thumbnailX = Utils.toShort(innerImageForm.getThumbnailX(), (short) 0);
                    short thumbnailY = Utils.toShort(innerImageForm.getThumbnailY(), (short) 0);
                    short imageMaxX = !innerImageForm.isNoResize() ? config.getInnerImageMaxWidth() : 0;
                    short imageMaxY = !innerImageForm.isNoResize() ? config.getInnerImageMaxHeight() : 0;
                    try {
                        String uploadedImageUuid = imageUploadManager.uploadStandard(
                                imageFile, thumbFlag, imageFlag,
                                (short) 0, (short) 0, thumbnailX, thumbnailY,
                                (short) 0, (short) 0, imageMaxX, imageMaxY);
                        if (!StringUtils.isEmpty(uploadedImageUuid)) {
                            innerImageForm.setImageUuid(uploadedImageUuid);
                        }
                    } catch (ImageUploadException e) {
                        e.setFieldName("imageFile");
                        throw e;
                    }
                    if (posting == null) {
                        return "noPosting";
                    }

                    return null;
                });

        if (!errors.hasErrors()) {
            return String.format("redirect:/%s#image-editing", posting.getGrpDetailsHref());
        } else {
            redirectAttributes.addFlashAttribute("errors", errors);
            redirectAttributes.addFlashAttribute("innerImageForm", innerImageForm);
            return "redirect:" + requestContext.getBack();
        }
    }

}
