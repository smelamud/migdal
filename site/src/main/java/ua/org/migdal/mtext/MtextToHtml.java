package ua.org.migdal.mtext;

import java.util.Map;

import org.xml.sax.helpers.DefaultHandler;

public class MtextToHtml extends DefaultHandler {

    private StringBuilder htmlBody = new StringBuilder();
    private StringBuilder htmlFootnotes = new StringBuilder();
    private MtextFormat format;
    private long id;
    private Map<Integer, InnerImageBlock> imageBlocks;

    public MtextToHtml(MtextFormat format, long id, Map<Integer, InnerImageBlock> imageBlocks) {
        this.format = format;
        this.id = id;
        this.imageBlocks = imageBlocks;
    }

    public String getHtmlBody() {
        return htmlBody.toString();
    }

    public String getHtmlFootnotes() {
        return htmlFootnotes.toString();
    }

}