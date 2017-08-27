package ua.org.migdal.form;

import java.io.Serializable;
import java.util.ArrayList;
import java.util.List;
import java.util.Map;
import java.util.function.Function;
import java.util.stream.Collectors;
import java.util.stream.StreamSupport;

import ua.org.migdal.data.Entry;
import ua.org.migdal.data.EntryType;

public class ReorderForm implements Serializable {

    private static final long serialVersionUID = 1965102197951598578L;

    private int entryType;
    private long[] ids;

    private transient List<Entry> entries;

    public ReorderForm() {
    }

    public ReorderForm(EntryType entryType) {
        this.entryType = entryType.ordinal();
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

    public List<Entry> getEntries() {
        return entries;
    }

    public void setEntries(Iterable<? extends Entry> entries) {
        if (ids == null) {
            ids = StreamSupport.stream(entries.spliterator(), false).mapToLong(Entry::getId).toArray();
        }
        Map<Long, Entry> entryMap = StreamSupport.stream(entries.spliterator(), false)
                                                 .collect(Collectors.toMap(Entry::getId, Function.identity()));
        this.entries = new ArrayList<>();
        for (long id : ids) {
            Entry entry = entryMap.get(id);
            if (entry != null) {
                this.entries.add(entry);
            }
        }
    }

}