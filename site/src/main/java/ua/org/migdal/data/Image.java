package ua.org.migdal.data;

import javax.persistence.DiscriminatorValue;
import javax.persistence.Entity;

@Entity
@DiscriminatorValue("4")
public class Image extends Entry {

    public static String validateHierarchy(Entry parent, Entry up, long id) {
        String errorCode = Entry.validateHierarchy(parent, up, id);
        if (errorCode != null) {
            return errorCode;
        }
        if (parent != null
                || up != null && up.getEntryType() != EntryType.POSTING && up.getEntryType() != EntryType.FORUM
                              && up.getEntryType() != EntryType.TOPIC) {
            return "hierarchy.incorrect";
        }
        return null;
    }

}