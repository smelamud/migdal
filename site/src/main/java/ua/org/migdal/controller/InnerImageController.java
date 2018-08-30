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
import ua.org.migdal.data.Image;
import ua.org.migdal.data.InnerImage;
import ua.org.migdal.data.Posting;
import ua.org.migdal.form.InnerImageForm;
import ua.org.migdal.grp.ImageTransformFlag;
import ua.org.migdal.grp.ThumbnailTransformFlag;
import ua.org.migdal.imageupload.ImageUploadException;
import ua.org.migdal.imageupload.ImageUploadManager;
import ua.org.migdal.location.LocationInfo;
import ua.org.migdal.manager.ImageFileManager;
import ua.org.migdal.manager.ImageManager;
import ua.org.migdal.manager.InnerImageManager;
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
    private InnerImageManager innerImageManager;

    @Inject
    private ImageManager imageManager;

    @Inject
    private PostingManager postingManager;

    @Inject
    private ImageUploadManager imageUploadManager;

    @Inject
    private ImageFileManager imageFileManager;

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

    @GetMapping("/edit-inner-image/{innerImageId}")
    public String innerImageEdit(
            @PathVariable long innerImageId,
            Model model) throws PageNotFoundException {

        InnerImage innerImage = innerImageManager.get(innerImageId);
        if (innerImage == null) {
            throw new PageNotFoundException();
        }
        Posting posting = postingManager.beg(innerImage.getEntry().getId());
        if (posting == null) {
            throw new PageNotFoundException();
        }

        innerImageEditLocationInfo(innerImage, posting, model);

        model.addAttribute("posting", posting);
        model.asMap().computeIfAbsent("innerImageForm", key -> new InnerImageForm(innerImage));
        return "inner-image-insert";
    }

    public LocationInfo innerImageEditLocationInfo(InnerImage innerImage, Posting posting, Model model) {
        return new LocationInfo(model)
                .withUri("/edit-inner-image/" + innerImage.getId())
                .withParent(postingViewController.postingViewLocationInfo(posting, model))
                .withPageTitle(String.format("Изменение картинки в статье \"%s\"", posting.getSubject()));
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
                    if (!posting.isWritable()) {
                        return "notWritable";
                    }
                    String errorCode = Image.validateHierarchy(null, posting, 0);
                    if (errorCode != null) {
                        return errorCode;
                    }
                    if (StringUtils.isEmpty(innerImageForm.getImageUuid())) {
                        return "imageFile.isEmpty";
                    }

                    InnerImage innerImage;
                    Image image;
                    if (innerImageForm.getId() > 0) {
                        innerImage = innerImageManager.get(innerImageForm.getId());
                        if (innerImage == null) {
                            return "noImage";
                        }
                        image = imageManager.beg(innerImage.getImage().getId());
                        if (image == null) {
                            return "noImage";
                        }
                    } else {
                        image = new Image(posting);
                        innerImage = new InnerImage(posting, image);
                    }

                    imageManager.store(
                            image,
                            im -> innerImageForm.toImage(im, imageFileManager),
                            innerImageForm.getId() <= 0,
                            innerImageForm.isTrackChanged(image),
                            innerImageForm.isCatalogChanged(image));
                    innerImageForm.toInnerImage(innerImage);
                    innerImageManager.save(innerImage);

                    return null;
                });

        if (!errors.hasErrors()) {
            return String.format("redirect:%s#image-editing", posting.getGrpDetailsHref());
        } else {
            redirectAttributes.addFlashAttribute("errors", errors);
            redirectAttributes.addFlashAttribute("innerImageForm", innerImageForm);
            return "redirect:" + requestContext.getBack();
        }
    }

}
