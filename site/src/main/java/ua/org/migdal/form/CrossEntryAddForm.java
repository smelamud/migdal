package ua.org.migdal.form;

import java.io.Serializable;
import javax.validation.constraints.NotBlank;
import javax.validation.constraints.Size;

public class CrossEntryAddForm implements Serializable {

    private static final long serialVersionUID = 826038977178847630L;

    @Size(max = 255)
    private String sourceName = "";

    private long sourceId;

    private int linkType;

    @NotBlank
    @Size(max = 75)
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
