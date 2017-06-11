package ua.org.migdal.data;

import java.util.ArrayList;
import java.util.List;

public class GroupUsersProjection {

    private long groupId;
    private String groupName;
    private List<IdLoginProjection> users = new ArrayList<>();

    public GroupUsersProjection(long groupId, String groupName) {
        this.groupId = groupId;
        this.groupName = groupName;
    }

    public long getGroupId() {
        return groupId;
    }

    public void setGroupId(long groupId) {
        this.groupId = groupId;
    }

    public String getGroupName() {
        return groupName;
    }

    public void setGroupName(String groupName) {
        this.groupName = groupName;
    }

    public List<IdLoginProjection> getUsers() {
        return users;
    }

}