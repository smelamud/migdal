package ua.org.migdal.mtext;

import ua.org.migdal.util.XmlUtils;
import ua.org.migdal.util.exception.XmlConverterException;

public class Mtext {

    private String xml;
    private MtextFormat format;
    private boolean ignoreWrongFormat;
    private long id;

    public Mtext(String xml, MtextFormat format) {
        this(xml, format, 0);
    }

    public Mtext(String xml, MtextFormat format, long id) {
        this.xml = xml;
        this.format = format;
        this.id = id;
        wrapXml();
    }

    private void wrapXml() {
        if (!XmlUtils.hasMarkup(xml)) {
            return;
        }
        xml = wrappedXml(format, xml);
    }

    private static String wrappedXml(MtextFormat format, String xml) {
        String rootTag = ("MTEXT-" + format.name()).toLowerCase();
        return String.format("<%s>%s</%s>", rootTag, xml, rootTag);
    }

    public String getXml() {
        return xml;
    }

    public String getXmlStrict() {
        return XmlUtils.hasMarkup(xml) ? xml : wrappedXml(format, xml);
    }

    public MtextFormat getFormat() {
        return format;
    }

    public boolean isIgnoreWrongFormat() {
        return ignoreWrongFormat;
    }

    public void setIgnoreWrongFormat(boolean ignoreWrongFormat) {
        this.ignoreWrongFormat = ignoreWrongFormat;
    }

    public long getId() {
        return id;
    }

    public int cleanLength() {
        try {
            return MtextShorten.cleanLength(xml);
        } catch (XmlConverterException e) {
            return 0;
        }
    }

    public Mtext shorten(int len, int mdlen, int pdlen) {
        return new Mtext(MtextShorten.shorten(xml, len, mdlen, pdlen), format, id);
    }

    public String shortenNote(int len, int mdlen, int pdlen) {
        return MtextShorten.shorten(xml, len, mdlen, pdlen, true, "...");
    }

}