package ua.org.migdal.util;

public class ImageFileInfo {

    private String extension;
    private String format;
    private long fileId;

    public ImageFileInfo(String extension, String format, long fileId) {
        this.extension = extension;
        this.format = format;
        this.fileId = fileId;
    }

    public String getExtension() {
        return extension;
    }

    public void setExtension(String extension) {
        this.extension = extension;
    }

    public String getFormat() {
        return format;
    }

    public void setFormat(String format) {
        this.format = format;
    }

    public long getFileId() {
        return fileId;
    }

    public void setFileId(long fileId) {
        this.fileId = fileId;
    }

}