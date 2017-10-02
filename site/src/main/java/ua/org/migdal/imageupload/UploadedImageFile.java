package ua.org.migdal.imageupload;

import ua.org.migdal.util.ImageFileUtils;

public class UploadedImageFile implements Cloneable {

    private long id;
    private short sizeX;
    private short sizeY;
    private long fileSize;
    private String format = "";
    private String originalFilename = "";

    public UploadedImageFile() {
    }

    public UploadedImageFile(long id, short sizeX, short sizeY, String format) {
        this.id = id;
        this.sizeX = sizeX;
        this.sizeY = sizeY;
        this.format = format;
    }

    public UploadedImageFile(long id, short sizeX, short sizeY, long fileSize, String format, String originalFilename) {
        this.id = id;
        this.sizeX = sizeX;
        this.sizeY = sizeY;
        this.fileSize = fileSize;
        this.format = format;
        this.originalFilename = originalFilename;
    }

    public long getId() {
        return id;
    }

    public void setId(long id) {
        this.id = id;
    }

    public short getSizeX() {
        return sizeX;
    }

    public void setSizeX(short sizeX) {
        this.sizeX = sizeX;
    }

    public short getSizeY() {
        return sizeY;
    }

    public void setSizeY(short sizeY) {
        this.sizeY = sizeY;
    }

    public long getFileSize() {
        return fileSize;
    }

    public void setFileSize(long fileSize) {
        this.fileSize = fileSize;
    }

    public long getFileSizeKb() {
        return (long) Math.ceil(getFileSize() / 1024f);
    }

    public String getFormat() {
        return format;
    }

    public void setFormat(String format) {
        this.format = format;
    }

    public String getOriginalFilename() {
        return originalFilename;
    }

    public void setOriginalFilename(String originalFilename) {
        this.originalFilename = originalFilename;
    }

    public String getUrl() {
        return getId() > 0 ? ImageFileUtils.imageUrl(getFormat(), getId()) : "";
    }

    public UploadedImageFile clone() {
        try {
            return (UploadedImageFile) super.clone();
        } catch (CloneNotSupportedException e) {
            return null;
        }
    }

}