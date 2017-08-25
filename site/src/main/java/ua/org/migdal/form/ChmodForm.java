package ua.org.migdal.form;

import java.io.Serializable;

import javax.validation.constraints.Size;

import ua.org.migdal.data.Entry;
import ua.org.migdal.data.EntryType;
import ua.org.migdal.data.Posting;
import ua.org.migdal.data.Topic;
import ua.org.migdal.data.User;
import ua.org.migdal.util.PermUtils;
import ua.org.migdal.util.Utils;

public class ChmodForm implements Serializable {

    private static final long serialVersionUID = -5075976742004579065L;

    private long id;

    private int entryType;

    @Size(max=30)
    private String userName = "";

    @Size(max=30)
    private String groupName = "";

    @Size(max=17)
    private String permString = "";

    private boolean recursive;

    public ChmodForm() {
    }

    private ChmodForm(EntryType entryType, Entry entry) {
        if (entry == null) {
            return;
        }

        id = entry.getId();
        this.entryType = entryType.ordinal();
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

    public int getEntryType() {
        return entryType;
    }

    public void setEntryType(int entryType) {
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

    public boolean isRecursive() {
        return recursive;
    }

    public void setRecursive(boolean recursive) {
        this.recursive = recursive;
    }

    public void toEntry(Entry entry, User user, User group) {
        entry.setUser(user);
        entry.setGroup(group);
        entry.setPerms(PermUtils.parse(getPermString()));
        entry.setModified(Utils.now());
    }

}