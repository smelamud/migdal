package ua.org.migdal.mtext;

public class MtextHtml {

    private String bodyHtml;
    private String footnotesHtml;

    public MtextHtml(String bodyHtml) {
        this(bodyHtml, null);
    }

    public MtextHtml(String bodyHtml, String footnotesHtml) {
        this.bodyHtml = bodyHtml;
        this.footnotesHtml = footnotesHtml;
    }

    public String getBodyHtml() {
        return bodyHtml;
    }

    public String getFootnotesHtml() {
        return footnotesHtml;
    }

}