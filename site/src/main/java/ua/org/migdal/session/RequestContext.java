package ua.org.migdal.session;

import java.util.Set;

import ua.org.migdal.data.User;

public interface RequestContext {

    String getLocation();

    String getSubdomain();

    boolean isWww();

    boolean isEnglish();

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

    String getUserGuestLogin();

    String getUserFolder();

    short getUserHidden();

    boolean isMigdalStudent();

    boolean isUserAdminUsers();

    boolean isUserAdminTopics();

    boolean isUserModerator();

    boolean isUserAdminDomain();

}