package ua.org.migdal.mtext;

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
    }

    public String getXml() {
        return xml;
    }

    public MtextFormat getFormat() {
        return format;
    }

    public boolean isIgnoreWrongFormat() {
        return ignoreWrongFormat;
    }

    public long getId() {
        return id;
    }

    public Mtext shorten(int len, int mdlen, int pdlen) {
        return new Mtext(MtextShorten.shorten(xml, len, mdlen, pdlen), format, id);
    }

    public Mtext shortenNote(int len, int mdlen, int pdlen) {
        return new Mtext(MtextShorten.shorten(xml, len, mdlen, pdlen, true, "..."), format, id);
    }

}