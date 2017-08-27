package ua.org.migdal.form;

import java.io.Serializable;

import ua.org.migdal.data.EntryType;

public class ReorderForm implements Serializable {

    private static final long serialVersionUID = 1965102197951598578L;

    private int entryType;

    private long[] ids;

    public ReorderForm() {
    }

    public ReorderForm(int entryType) {
        this.entryType = entryType;
    }

    public ReorderForm(EntryType entryType) {
        this(entryType.ordinal());
    }

    public int getEntryType() {
        return entryType;
    }

    public void setEntryType(int entryType) {
        this.entryType = entryType;
    }

    public long[] getIds() {
        return ids;
    }

    public void setIds(long[] ids) {
        this.ids = ids;
    }

}