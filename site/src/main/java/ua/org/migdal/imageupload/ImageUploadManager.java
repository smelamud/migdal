package ua.org.migdal.imageupload;

import java.util.HashMap;
import java.util.Map;
import java.util.UUID;

import org.springframework.stereotype.Service;
import ua.org.migdal.data.Entry;

@Service
public class ImageUploadManager {

    private Map<UUID, UploadedImage> uploads = new HashMap<>();

    public UploadedImage get(String uuid) {
        UploadedImage uploadedImage = uploads.get(UUID.fromString(uuid));
        uploadedImage.access();
        return uploadedImage;
    }

    public String create() {
        UUID uuid = UUID.randomUUID();
        uploads.put(uuid, new UploadedImage());
        return uuid.toString();
    }

    public String extract(Entry entry) {
        UUID uuid = UUID.randomUUID();
        uploads.put(uuid, new UploadedImage(entry));
        return uuid.toString();
    }

}