package ua.org.migdal.data;

import java.io.Serializable;
import java.util.Objects;
import javax.persistence.Embeddable;
import javax.validation.constraints.NotNull;
import javax.validation.constraints.Size;

@Embeddable
public class OldEntryId implements Serializable {

    private static final long serialVersionUID = 3388925970414490248L;

    @NotNull
    @Size(max=32)
    private String tableName = "";

    @NotNull
    private long oldId;

    public String getTableName() {
        return tableName;
    }

    public void setTableName(String tableName) {
        this.tableName = tableName;
    }

    public long getOldId() {
        return oldId;
    }

    public void setOldId(long oldId) {
        this.oldId = oldId;
    }

    @Override
    public int hashCode() {
        return Objects.hash(tableName, oldId);
    }

    @Override
    public boolean equals(Object obj) {
        if (!(obj instanceof OldEntryId)) {
            return false;
        }
        OldEntryId peer = (OldEntryId) obj;
        return tableName.equals(peer.tableName) && oldId == peer.oldId;
    }

}
