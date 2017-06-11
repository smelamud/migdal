package ua.org.migdal.form;

import javax.validation.constraints.Size;

import org.hibernate.validator.constraints.NotBlank;

public class GroupAddForm {

    @NotBlank
    @Size(max=30)
    private String groupName = "";

    @NotBlank
    @Size(max=30)
    private String userName = "";

    public GroupAddForm() {
    }

    public GroupAddForm(String groupName, String userName) {
        if (groupName != null) {
            this.groupName = groupName;
        }
        if (userName != null) {
            this.userName = userName;
        }
    }

    public String getGroupName() {
        return groupName;
    }

    public void setGroupName(String groupName) {
        this.groupName = groupName;
    }

    public String getUserName() {
        return userName;
    }

    public void setUserName(String userName) {
        this.userName = userName;
    }

}