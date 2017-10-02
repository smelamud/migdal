package ua.org.migdal.imageupload;

import java.util.HashMap;
import java.util.Map;
import java.util.UUID;

import javax.annotation.PostConstruct;

import org.springframework.stereotype.Service;
import org.springframework.util.StringUtils;
import org.springframework.web.multipart.MultipartFile;

import ua.org.migdal.data.Entry;
import ua.org.migdal.data.ImageFileTransformType;
import ua.org.migdal.grp.ImageTransformFlag;
import ua.org.migdal.grp.ThumbnailTransformFlag;

@Service
public class ImageUploadManager {

    private static ImageUploadManager instance;

    private Map<UUID, UploadedImage> uploads = new HashMap<>();

    @PostConstruct
    public void init() {
        instance = this;
    }

    public static ImageUploadManager getInstance() {
        return instance;
    }

    public UploadedImage get(String uuid) {
        if (StringUtils.isEmpty(uuid)) {
            return null;
        }

        try {
            UploadedImage uploadedImage = uploads.get(UUID.fromString(uuid));
            if (uploadedImage != null) {
                uploadedImage.access();
            }
            return uploadedImage;
        } catch (IllegalArgumentException e) {
            return null;
        }
    }

    public String extract(Entry entry) {
        if (entry.getSmallImage() == null && entry.getLargeImage() == null) {
            return "";
        }

        UUID uuid = UUID.randomUUID();
        uploads.put(uuid, new UploadedImage(entry));
        return uuid.toString();
    }

    public String uploadStandard(MultipartFile imageFile,
                                 ThumbnailTransformFlag thumbFlag, ImageTransformFlag imageFlag,
                                 short thumbExactX, short thumbExactY, short thumbMaxX, short thumbMaxY,
                                 short imageExactX, short imageExactY, short imageMaxX, short imageMaxY) {
        if (imageFile == null || imageFile.isEmpty()) {
            return "";
        }

        short exactX;
        short exactY;
        short maxX;
        short maxY;
        ImageFileTransformType transform;
        short transformX;
        short transformY;

        switch (imageFlag) {
            case RESIZE:
                exactX = 0;
                exactY = 0;
                maxX = 0;
                maxY = 0;
                transform = ImageFileTransformType.RESIZE;
                transformX = imageMaxX;
                transformY = imageMaxY;
                break;

            case MANUAL:
            default:
                exactX = imageExactX;
                exactY = imageExactY;
                maxX = imageMaxX;
                maxY = imageMaxY;
                transform = ImageFileTransformType.NULL;
                transformX = 0;
                transformY = 0;
        }
        UploadedImageFile largeImageFile = uploadImageFile(imageFile, exactX, exactY, maxX, maxY,
                transform, transformX, transformY);
        if (largeImageFile == null) {
            return "";
        }

        UploadedImageFile smallImageFile = null;

        switch (thumbFlag) {
            case AUTO:
                smallImageFile = thumbnailImageFile(largeImageFile, ImageFileTransformType.RESIZE,
                        thumbMaxX, thumbMaxY);
                break;

            case CLIP:
                smallImageFile = thumbnailImageFile(largeImageFile, ImageFileTransformType.CLIP,
                        thumbExactX, thumbExactY);
                break;

            case NONE:
            default:
                break;
        }

        if (smallImageFile == null || smallImageFile.getId() == 0) {
            smallImageFile = largeImageFile.clone();
        }
        largeImageFile.setFileSize(imageFile.getSize());
        largeImageFile.setOriginalFilename(imageFile.getOriginalFilename());

        UUID uuid = UUID.randomUUID();
        uploads.put(uuid, new UploadedImage(smallImageFile, largeImageFile));
        return uuid.toString();
    }

    private UploadedImageFile uploadImageFile(MultipartFile imageFile, short exactX, short exactY,
                                              short maxX, short maxY, ImageFileTransformType transform,
                                              short transformX, short transformY) {
        return null;
    }

    private UploadedImageFile thumbnailImageFile(UploadedImageFile imageFile, ImageFileTransformType transform,
                                                 short transformX, short transformY) {
        return null;
    }
}