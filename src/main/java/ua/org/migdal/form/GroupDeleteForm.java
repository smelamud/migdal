package ua.org.migdal.form;

import java.io.Serializable;

public class GroupDeleteForm implements Serializable {

    private static final long serialVersionUID = -48825897149078130L;

    private long groupId;
    private long userId;

    public GroupDeleteForm() {
    }

    public long getGroupId() {
        return groupId;
    }

    public void setGroupId(long groupId) {
        this.groupId = groupId;
    }

    public long getUserId() {
        return userId;
    }

    public void setUserId(long userId) {
        this.userId = userId;
    }

}