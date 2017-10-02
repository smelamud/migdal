package ua.org.migdal.imageupload;

import java.time.Instant;

import ua.org.migdal.data.Entry;

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

}