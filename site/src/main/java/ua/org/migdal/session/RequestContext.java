package ua.org.migdal.session;

import java.util.Set;

import ua.org.migdal.data.User;

public interface RequestContext {

    void temporarySession(User user, long realUserId);

    String getLocation();

    String getSubdomain();

    boolean isWww();

    boolean isEnglish();

    String getIp();

    String getBack();

    boolean isHasBack();

    String getOrigin();

    boolean isPrintMode();

    long getUserId();

    User getUser();

    boolean isLogged();

    long getRealUserId();

    User getRealUser();

    Set<Long> getUserGroups();

    String getUserLogin();

    String getUserGuestLoginHint();

    void setUserGuestLoginHint(String userGuestLoginHint);

    String getUserFolder();

    short getUserHidden();

    boolean isMigdalStudent();

    boolean isUserAdminUsers();

    boolean isUserAdminTopics();

    boolean isUserModerator();

    boolean isUserAdminDomain();

}