package ua.org.migdal.data;

import javax.persistence.EmbeddedId;
import javax.persistence.Entity;
import javax.persistence.FetchType;
import javax.persistence.JoinColumn;
import javax.persistence.ManyToOne;
import javax.persistence.Table;
import javax.validation.constraints.NotNull;
import javax.validation.constraints.Size;

@Entity
@Table(name="old_ids")
public class OldId {

    @EmbeddedId
    private OldEntryId oldEntryId;

    @Size(max=75)
    private String oldIdent;

    @NotNull
    @ManyToOne(fetch=FetchType.LAZY)
    @JoinColumn(name="entry_id")
    private Entry entry;

    public OldEntryId getOldEntryId() {
        return oldEntryId;
    }

    public void setOldEntryId(OldEntryId oldEntryId) {
        this.oldEntryId = oldEntryId;
    }

    public String getOldIdent() {
        return oldIdent;
    }

    public void setOldIdent(String oldIdent) {
        this.oldIdent = oldIdent;
    }

    public Entry getEntry() {
        return entry;
    }

    public void setEntry(Entry entry) {
        this.entry = entry;
    }

}
