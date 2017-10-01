package ua.org.migdal.imageupload;

import java.util.HashMap;
import java.util.Map;
import java.util.UUID;

import javax.annotation.PostConstruct;

import org.springframework.stereotype.Service;
import org.springframework.util.StringUtils;
import ua.org.migdal.data.Entry;

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
            uploadedImage.access();
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

}