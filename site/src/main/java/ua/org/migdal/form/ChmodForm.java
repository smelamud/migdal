package ua.org.migdal.form;

import java.io.Serializable;

import javax.validation.constraints.Size;

import org.hibernate.validator.constraints.NotBlank;
import ua.org.migdal.data.Entry;
import ua.org.migdal.data.EntryType;
import ua.org.migdal.data.Posting;
import ua.org.migdal.data.Topic;

public class ChmodForm implements Serializable {

    private static final long serialVersionUID = -5075976742004579065L;

    private long id;

    @NotBlank
    private EntryType entryType;

    @NotBlank
    @Size(max=30)
    private String userName = "";

    @NotBlank
    @Size(max=30)
    private String groupName = "";

    @NotBlank
    @Size(max=17)
    private String permString = "";

    private short recursive;

    public ChmodForm() {
    }

    private ChmodForm(EntryType entryType, Entry entry) {
        if (entry == null) {
            return;
        }

        id = entry.getId();
        userName = entry.getUser().getLogin();
        groupName = entry.getGroup().getLogin();
        permString = entry.getPermString();
    }

    public ChmodForm(Topic topic) {
        this(EntryType.TOPIC, topic);
    }

    public ChmodForm(Posting posting) {
        this(EntryType.POSTING, posting);
    }

    public long getId() {
        return id;
    }

    public void setId(long id) {
        this.id = id;
    }

    public EntryType getEntryType() {
        return entryType;
    }

    public void setEntryType(EntryType entryType) {
        this.entryType = entryType;
    }

    public String getUserName() {
        return userName;
    }

    public void setUserName(String userName) {
        this.userName = userName;
    }

    public String getGroupName() {
        return groupName;
    }

    public void setGroupName(String groupName) {
        this.groupName = groupName;
    }

    public String getPermString() {
        return permString;
    }

    public void setPermString(String permString) {
        this.permString = permString;
    }

    public short getRecursive() {
        return recursive;
    }

    public void setRecursive(short recursive) {
        this.recursive = recursive;
    }

}