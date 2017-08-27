package ua.org.migdal.manager;

import ua.org.migdal.data.Entry;

public interface EntryManagerBase {

    <T extends Entry> T beg(long id);

    void save(Entry entry);

}