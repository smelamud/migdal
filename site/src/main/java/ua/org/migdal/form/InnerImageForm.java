package ua.org.migdal.form;

import java.beans.Transient;
import java.io.Serializable;

import ua.org.migdal.data.ImagePlacement;
import ua.org.migdal.imageupload.ImageUploadManager;
import ua.org.migdal.imageupload.UploadedImage;

public class InnerImageForm implements Serializable {

    private static final long serialVersionUID = 7131030172450410235L;

    private long id;

    private long postingId;

    private long imageId;

    private int paragraph;

    private int x;

    private int y;

    private short placement = ImagePlacement.BOTTOMLEFT;

    private String imageUuid = "";

    private String title = "";

    private boolean noResize;

    private boolean thumbnail;

    private String thumbnailX = "";

    private String thumbnailY = "";

    public InnerImageForm() {
    }

    public InnerImageForm(long postingId, int paragraph, int x, int y) {
        this.postingId = postingId;
        this.paragraph = paragraph;
        this.x = x;
        this.y = y;
    }

    public long getId() {
        return id;
    }

    public void setId(long id) {
        this.id = id;
    }

    public long getPostingId() {
        return postingId;
    }

    public void setPostingId(long postingId) {
        this.postingId = postingId;
    }

    public long getImageId() {
        return imageId;
    }

    public void setImageId(long imageId) {
        this.imageId = imageId;
    }

    public int getParagraph() {
        return paragraph;
    }

    public void setParagraph(int paragraph) {
        this.paragraph = paragraph;
    }

    public int getX() {
        return x;
    }

    public void setX(int x) {
        this.x = x;
    }

    public int getY() {
        return y;
    }

    public void setY(int y) {
        this.y = y;
    }

    public short getPlacement() {
        return placement;
    }

    public void setPlacement(short placement) {
        this.placement = placement;
    }

    public String getImageUuid() {
        return imageUuid;
    }

    public void setImageUuid(String imageUuid) {
        this.imageUuid = imageUuid;
    }

    @Transient
    public UploadedImage getImage() {
        return ImageUploadManager.getInstance().get(getImageUuid());
    }

    public String getTitle() {
        return title;
    }

    public void setTitle(String title) {
        this.title = title;
    }

    public boolean isNoResize() {
        return noResize;
    }

    public void setNoResize(boolean noResize) {
        this.noResize = noResize;
    }

    public boolean isThumbnail() {
        return thumbnail;
    }

    public void setThumbnail(boolean thumbnail) {
        this.thumbnail = thumbnail;
    }

    public String getThumbnailX() {
        return thumbnailX;
    }

    public void setThumbnailX(String thumbnailX) {
        this.thumbnailX = thumbnailX;
    }

    public String getThumbnailY() {
        return thumbnailY;
    }

    public void setThumbnailY(String thumbnailY) {
        this.thumbnailY = thumbnailY;
    }

}
