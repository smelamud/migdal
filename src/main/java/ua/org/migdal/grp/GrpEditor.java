package ua.org.migdal.grp;

import org.springframework.util.StringUtils;

public class GrpEditor {

    private String field;
    private String title;
    private String comment = "";
    private boolean mandatory = false;

    private String style = "";
    private short imageExactX;
    private short imageExactY;
    private short imageMaxX;
    private short imageMaxY;
    private short thumbExactX;
    private short thumbExactY;
    private short thumbMaxX;
    private short thumbMaxY;
    private String whatN = "";
    private String whatG = "";
    private String whatA = "";
    private String whatAs = "";
    private String what = "";

    public GrpEditor() {
    }

    public String getField() {
        return field;
    }

    public void setField(String field) {
        this.field = field;
    }

    public boolean isSection() {
        return StringUtils.isEmpty(field);
    }

    public String getTitle() {
        return title;
    }

    public void setTitle(String title) {
        this.title = title;
    }

    public String getComment() {
        return comment;
    }

    public void setComment(String comment) {
        this.comment = comment;
    }

    public boolean isMandatory() {
        return mandatory;
    }

    public void setMandatory(boolean mandatory) {
        this.mandatory = mandatory;
    }

    public String getStyle() {
        return style;
    }

    public void setStyle(String style) {
        this.style = style;
    }

    public ThumbnailTransformFlag getThumbnailStyle() {
        return ThumbnailTransformFlag.parse(getStyle().split("-")[0]);
    }

    public ImageTransformFlag getImageStyle() {
        return ImageTransformFlag.parse(getStyle().split("-")[1]);
    }

    public short getImageExactX() {
        return imageExactX;
    }

    public void setImageExactX(short imageExactX) {
        this.imageExactX = imageExactX;
    }

    public short getImageExactY() {
        return imageExactY;
    }

    public void setImageExactY(short imageExactY) {
        this.imageExactY = imageExactY;
    }

    private String formatDimensions(short x, short y) {
        if (x > 0) {
            if (y > 0) {
                return String.format("%d x %d пикселов", x, y);
            } else {
                return String.format("%d пикселов по ширине", x);
            }
        } else {
            if (y > 0) {
                return String.format("%d пикселов по высоте", y);
            } else {
                return "";
            }
        }
    }
    
    public String getImageExact() {
        return formatDimensions(getImageExactX(), getImageExactY());
    }

    public short getImageMaxX() {
        return imageMaxX;
    }

    public void setImageMaxX(short imageMaxX) {
        this.imageMaxX = imageMaxX;
    }

    public short getImageMaxY() {
        return imageMaxY;
    }

    public void setImageMaxY(short imageMaxY) {
        this.imageMaxY = imageMaxY;
    }

    public String getImageMax() {
        return formatDimensions(getImageMaxX(), getImageMaxY());
    }

    public short getThumbExactX() {
        return thumbExactX;
    }

    public void setThumbExactX(short thumbExactX) {
        this.thumbExactX = thumbExactX;
    }

    public short getThumbExactY() {
        return thumbExactY;
    }

    public void setThumbExactY(short thumbExactY) {
        this.thumbExactY = thumbExactY;
    }

    public short getThumbMaxX() {
        return thumbMaxX;
    }

    public void setThumbMaxX(short thumbMaxX) {
        this.thumbMaxX = thumbMaxX;
    }

    public short getThumbMaxY() {
        return thumbMaxY;
    }

    public void setThumbMaxY(short thumbMaxY) {
        this.thumbMaxY = thumbMaxY;
    }

    public String getWhatN() {
        return whatN;
    }

    public void setWhatN(String whatN) {
        this.whatN = whatN;
    }

    public String getWhatG() {
        return whatG;
    }

    public void setWhatG(String whatG) {
        this.whatG = whatG;
    }

    public String getWhatA() {
        return whatA;
    }

    public void setWhatA(String whatA) {
        this.whatA = whatA;
    }

    public String getWhatAs() {
        return whatAs;
    }

    public void setWhatAs(String whatAs) {
        this.whatAs = whatAs;
    }

    public String getWhat() {
        return what;
    }

    public void setWhat(String what) {
        this.what = what;
    }

}