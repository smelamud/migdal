package ua.org.migdal.data;

import javax.persistence.Access;
import javax.persistence.AccessType;
import javax.persistence.Entity;
import javax.persistence.Enumerated;
import javax.persistence.FetchType;
import javax.persistence.GeneratedValue;
import javax.persistence.Id;
import javax.persistence.JoinColumn;
import javax.persistence.ManyToOne;
import javax.persistence.Table;
import javax.validation.constraints.NotNull;
import javax.validation.constraints.Size;

@Entity
@Table(name="cross_entries")
public class CrossEntry {

    @Id
    @GeneratedValue
    @Access(AccessType.PROPERTY)
    private long id;

    @NotNull
    @Size(max=255)
    private String sourceName = "";

    @ManyToOne(fetch=FetchType.LAZY)
    @JoinColumn(name="source_id")
    private Entry source;

    @NotNull
    @Enumerated
    private LinkType linkType = LinkType.NONE;

    @Size(max=255)
    private String peerName;

    @ManyToOne(fetch=FetchType.EAGER)
    @JoinColumn(name="peer_id")
    private Entry peer;

    @NotNull
    @Size(max=255)
    private String peerPath = "";

    @NotNull
    @Size(max=255)
    private String peerSubject = "";

    @NotNull
    @Size(max=64)
    private String peerIcon = "";

    public long getId() {
        return id;
    }

    public void setId(long id) {
        this.id = id;
    }

    public String getSourceName() {
        return sourceName;
    }

    public void setSourceName(String sourceName) {
        this.sourceName = sourceName;
    }

    public Entry getSource() {
        return source;
    }

    public void setSource(Entry source) {
        this.source = source;
    }

    public LinkType getLinkType() {
        return linkType;
    }

    public void setLinkType(LinkType linkType) {
        this.linkType = linkType;
    }

    public String getPeerName() {
        return peerName;
    }

    public void setPeerName(String peerName) {
        this.peerName = peerName;
    }

    public Entry getPeer() {
        return peer;
    }

    public void setPeer(Entry peer) {
        this.peer = peer;
    }

    public String getPeerPath() {
        return peerPath;
    }

    public void setPeerPath(String peerPath) {
        this.peerPath = peerPath;
    }

    public String getPeerSubject() {
        return peerSubject;
    }

    public void setPeerSubject(String peerSubject) {
        this.peerSubject = peerSubject;
    }

    public String getPeerIcon() {
        return peerIcon;
    }

    public void setPeerIcon(String peerIcon) {
        this.peerIcon = peerIcon;
    }

}