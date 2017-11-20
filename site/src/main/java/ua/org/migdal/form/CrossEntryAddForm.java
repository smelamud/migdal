package ua.org.migdal.form;

import java.io.Serializable;
import javax.validation.constraints.NotBlank;
import javax.validation.constraints.Size;

import org.springframework.util.StringUtils;
import ua.org.migdal.data.CrossEntry;
import ua.org.migdal.data.Entry;
import ua.org.migdal.data.LinkType;
import ua.org.migdal.data.Posting;

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

    public void toCrossEntry(CrossEntry crossEntry, Entry source, Entry peer) {
        if (!StringUtils.isEmpty(getSourceName())) {
            crossEntry.setSourceName(getSourceName());
        }
        crossEntry.setSource(source);
        crossEntry.setLinkType(LinkType.valueOf(getLinkType()));
        crossEntry.setPeer(peer);
        crossEntry.setPeerSubject(peer.getSubject());
        if (peer instanceof Posting) {
            crossEntry.setPeerPath(((Posting) peer).getGrpDetailsHref());
        } else {
            crossEntry.setPeerPath("/" + peer.getCatalog());
        }
        crossEntry.setPeerIcon("topic");
    }

}
