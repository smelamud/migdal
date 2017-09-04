package ua.org.migdal.grp;

import org.springframework.util.StringUtils;

public class GrpEditor {

    private String field;
    private String title;
    private String comment = "";
    private boolean mandatory = false;

    private String style = "";
    private int imageExactX;
    private int imageExactY;
    private int imageMaxX;
    private int imageMaxY;
    private int thumbExactX;
    private int thumbExactY;
    private int thumbMaxX;
    private int thumbMaxY;
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

    public int getImageExactX() {
        return imageExactX;
    }

    public void setImageExactX(int imageExactX) {
        this.imageExactX = imageExactX;
    }

    public int getImageExactY() {
        return imageExactY;
    }

    public void setImageExactY(int imageExactY) {
        this.imageExactY = imageExactY;
    }

    public int getImageMaxX() {
        return imageMaxX;
    }

    public void setImageMaxX(int imageMaxX) {
        this.imageMaxX = imageMaxX;
    }

    public int getImageMaxY() {
        return imageMaxY;
    }

    public void setImageMaxY(int imageMaxY) {
        this.imageMaxY = imageMaxY;
    }

    public int getThumbExactX() {
        return thumbExactX;
    }

    public void setThumbExactX(int thumbExactX) {
        this.thumbExactX = thumbExactX;
    }

    public int getThumbExactY() {
        return thumbExactY;
    }

    public void setThumbExactY(int thumbExactY) {
        this.thumbExactY = thumbExactY;
    }

    public int getThumbMaxX() {
        return thumbMaxX;
    }

    public void setThumbMaxX(int thumbMaxX) {
        this.thumbMaxX = thumbMaxX;
    }

    public int getThumbMaxY() {
        return thumbMaxY;
    }

    public void setThumbMaxY(int thumbMaxY) {
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