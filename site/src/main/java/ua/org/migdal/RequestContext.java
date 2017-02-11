package ua.org.migdal;

import java.util.List;

public interface RequestContext {

    long getUserId();

    void setUserId(long userId);

    long getRealUserId();

    void setRealUserId(long realUserId);

    List<Long> getUserGroups();

    void setUserGroups(List<Long> userGroups);

    String getUserLogin();

    void setUserLogin(String userLogin);

    String getUserFolder();

    void setUserFolder(String userFolder);

    short getUserHidden();

    void setUserHidden(short userHidden);

    boolean isUserAdminUsers();

    void setUserAdminUsers(boolean userAdminUsers);

    boolean isUserAdminTopics();

    void setUserAdminTopics(boolean userAdminTopics);

    boolean isUserModerator();

    void setUserModerator(boolean userModerator);

    boolean isUserAdminDomain();

    void setUserAdminDomain(boolean userAdminDomain);

}