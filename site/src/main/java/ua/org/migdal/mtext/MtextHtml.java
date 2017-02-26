package ua.org.migdal.mtext;

public class MtextHtml {

    private CharSequence bodyHtml;
    private CharSequence footnotesHtml;

    public MtextHtml(String bodyHtml) {
        this(bodyHtml, null);
    }

    public MtextHtml(CharSequence bodyHtml, CharSequence footnotesHtml) {
        this.bodyHtml = bodyHtml;
        this.footnotesHtml = footnotesHtml;
    }

    public CharSequence getBodyHtml() {
        return bodyHtml;
    }

    public CharSequence getFootnotesHtml() {
        return footnotesHtml;
    }

}