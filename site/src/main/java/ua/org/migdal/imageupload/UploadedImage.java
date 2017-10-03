package ua.org.migdal.imageupload;

import java.time.Instant;

import ua.org.migdal.data.Entry;
import ua.org.migdal.data.ImageFile;
import ua.org.migdal.manager.ImageFileManager;

public class UploadedImage {

    private UploadedImageFile small = new UploadedImageFile();
    private UploadedImageFile large = new UploadedImageFile();

    private Instant lastAccessed = Instant.now();

    public UploadedImage() {
    }

    public UploadedImage(UploadedImageFile small, UploadedImageFile large) {
        this.small = small;
        this.large = large;
    }

    public UploadedImage(Entry entry) {
        if (entry.getSmallImage() == null && entry.getLargeImage() == null) {
            return;
        }
        
        if (entry.getLargeImage() != null) {
            large = new UploadedImageFile(
                    entry.getLargeImage().getId(),
                    entry.getLargeImageX(),
                    entry.getLargeImageY(),
                    entry.getLargeImageSize(),
                    entry.getLargeImageFormat(),
                    entry.getLargeImageFilename());
        } else {
            large = new UploadedImageFile(
                    entry.getSmallImage().getId(),
                    entry.getSmallImageX(),
                    entry.getSmallImageY(),
                    entry.getLargeImageSize(),
                    entry.getSmallImageFormat(),
                    entry.getLargeImageFilename());
        }
        small = new UploadedImageFile(
                entry.getSmallImage().getId(),
                entry.getSmallImageX(),
                entry.getSmallImageY(),
                entry.getSmallImageFormat());
    }
    
    public UploadedImageFile getSmall() {
        return small;
    }

    public UploadedImageFile getLarge() {
        return large;
    }

    public Instant getLastAccessed() {
        return lastAccessed;
    }

    public void access() {
        lastAccessed = Instant.now();
    }

    public void toEntry(Entry entry, ImageFileManager imageFileManager) {
        ImageFile largeImage = imageFileManager.get(getLarge().getId());
        entry.setSmallImage(imageFileManager.get(getSmall().getId()));
        entry.setSmallImageX(getSmall().getSizeX());
        entry.setSmallImageY(getSmall().getSizeY());
        entry.setSmallImageFormat(getSmall().getFormat());
        entry.setLargeImage(imageFileManager.get(getLarge().getId()));
        entry.setLargeImageX(getLarge().getSizeX());
        entry.setLargeImageY(getLarge().getSizeY());
        entry.setLargeImageFormat(getLarge().getFormat());
        entry.setLargeImageSize(getLarge().getFileSize());
        entry.setLargeImageFilename(getLarge().getOriginalFilename());
    }

    public static void clearEntry(Entry entry) {
        entry.setSmallImage(null);
        entry.setSmallImageX((short) 0);
        entry.setSmallImageY((short) 0);
        entry.setSmallImageFormat("");
        entry.setLargeImage(null);
        entry.setLargeImageX((short) 0);
        entry.setLargeImageY((short) 0);
        entry.setLargeImageFormat("");
        entry.setLargeImageSize(0);
        entry.setLargeImageFilename("");
    }

}