package ua.org.migdal.form;

import java.beans.Transient;
import java.io.Serializable;

import javax.validation.constraints.Size;

import ua.org.migdal.Config;
import ua.org.migdal.data.Image;
import ua.org.migdal.data.ImagePlacement;
import ua.org.migdal.data.InnerImage;
import ua.org.migdal.imageupload.ImageUploadManager;
import ua.org.migdal.imageupload.UploadedImage;
import ua.org.migdal.manager.ImageFileManager;
import ua.org.migdal.mtext.MtextFormat;
import ua.org.migdal.text.Text;
import ua.org.migdal.text.TextFormat;

public class InnerImageForm implements Serializable {

    private static final long serialVersionUID = 7131030172450410235L;

    private long id;

    private long postingId;

    private int paragraph;

    private int x;

    private int y;

    private short placement = ImagePlacement.BOTTOMLEFT;

    @Size(max = 40)
    private String imageUuid = "";

    @Size(max = 250)
    private String title = "";

    private boolean noResize;

    private boolean thumbnail;

    @Size(max = 5)
    private String thumbnailX = "";

    @Size(max = 5)
    private String thumbnailY = "";

    public InnerImageForm() {
    }

    public InnerImageForm(long postingId, int paragraph, int x, int y) {
        this.postingId = postingId;
        this.paragraph = paragraph;
        this.x = x;
        this.y = y;
    }

    public InnerImageForm(InnerImage innerImage) {
        id = innerImage.getId();
        postingId = innerImage.getEntry().getId();
        paragraph = innerImage.getParagraph();
        x = innerImage.getX();
        y = innerImage.getY();
        placement = innerImage.getPlacement();
        imageUuid = ImageUploadManager.getInstance().extract(innerImage.getImage());
        title = innerImage.getImage().getTitle();
        UploadedImage uploadedImage = ImageUploadManager.getInstance().get(imageUuid);
        noResize = uploadedImage.getLarge().getSizeX() > Config.getInstance().getInnerImageMaxWidth()
                || uploadedImage.getLarge().getSizeY() > Config.getInstance().getInnerImageMaxHeight();
        thumbnail = uploadedImage.isThumbnailed();
        if (thumbnail) {
            thumbnailX = Short.toString(uploadedImage.getSmall().getSizeX());
            thumbnailY = Short.toString(uploadedImage.getSmall().getSizeY());
        }
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

    public void toInnerImage(InnerImage innerImage) {
        innerImage.setParagraph(getParagraph());
        innerImage.setX(getX());
        innerImage.setY(getY());
        innerImage.setPlacement(getPlacement());
    }

    public void toImage(Image image, ImageFileManager imageFileManager) {
        image.setBodyFormat(TextFormat.PLAIN);
        image.setTitle(Text.convertLigatures(getTitle()));
        image.setTitleXml(Text.convert(image.getTitle(), image.getBodyFormat(), MtextFormat.LINE));
        if (getImage() != null) {
            getImage().toEntry(image, imageFileManager);
        } else {
            UploadedImage.clearEntry(image);
        }
    }

    public boolean isTrackChanged(Image image) {
        return getId() > 0 && getPostingId() != image.getUpId();
    }

    public boolean isCatalogChanged(Image image) {
        return getId() > 0 && getPostingId() != image.getUpId();
    }

}
