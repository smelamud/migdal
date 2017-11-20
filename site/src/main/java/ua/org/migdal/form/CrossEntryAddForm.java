package ua.org.migdal.form;

import java.io.Serializable;

public class CrossEntryAddForm implements Serializable {

    private static final long serialVersionUID = 826038977178847630L;

    private String sourceName = "";

    private long sourceId;

    private int linkType;

    private String peerIdent = "";

    public CrossEntryAddForm() {
    }

    public CrossEntryAddForm(String sourceName, long sourceId, int linkType) {
        if (sourceName != null) {
            this.sourceName = sourceName;
        }
        this.sourceId = sourceId;
        this.linkType = linkType;
    }

    public String getSourceName() {
        return sourceName;
    }

    public void setSourceName(String sourceName) {
        this.sourceName = sourceName;
    }

    public long getSourceId() {
        return sourceId;
    }

    public void setSourceId(long sourceId) {
        this.sourceId = sourceId;
    }

    public int getLinkType() {
        return linkType;
    }

    public void setLinkType(int linkType) {
        this.linkType = linkType;
    }

    public String getPeerIdent() {
        return peerIdent;
    }

    public void setPeerIdent(String peerIdent) {
        this.peerIdent = peerIdent;
    }

}
