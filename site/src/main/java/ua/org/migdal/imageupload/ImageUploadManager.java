package ua.org.migdal.imageupload;

import java.util.HashMap;
import java.util.Map;
import java.util.UUID;

import javax.annotation.PostConstruct;

import org.springframework.stereotype.Service;
import org.springframework.util.StringUtils;
import org.springframework.web.multipart.MultipartFile;
import ua.org.migdal.data.Entry;
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
        return "";
    }

}