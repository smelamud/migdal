package ua.org.migdal.imageupload;

import ua.org.migdal.util.ImageFileUtils;

public class UploadedImageFile {

    private long id;
    private short sizeX;
    private short sizeY;
    private long fileSize;
    private String format = "";
    private String filename = "";

    public UploadedImageFile() {
    }

    public UploadedImageFile(long id, short sizeX, short sizeY, String format) {
        this.id = id;
        this.sizeX = sizeX;
        this.sizeY = sizeY;
        this.format = format;
    }

    public UploadedImageFile(long id, short sizeX, short sizeY, long fileSize, String format, String filename) {
        this.id = id;
        this.sizeX = sizeX;
        this.sizeY = sizeY;
        this.fileSize = fileSize;
        this.format = format;
        this.filename = filename;
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

    public String getFilename() {
        return filename;
    }

    public void setFilename(String filename) {
        this.filename = filename;
    }

    public String getUrl() {
        return getId() > 0 ? ImageFileUtils.imageUrl(getFormat(), getId()) : "";
    }

}