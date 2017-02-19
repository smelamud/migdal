package ua.org.migdal.session;

import java.util.List;

public interface RequestContext {

    String getSubdomain();

    boolean isWww();

    boolean isEnglish();

    String getBack();

    boolean isHasBack();

    boolean isPrintMode();

    long getUserId();

    boolean isLogged();

    long getRealUserId();

    List<Long> getUserGroups();

    String getUserLogin();

    String getUserFolder();

    short getUserHidden();

    boolean isMigdalStudent();

    boolean isUserAdminUsers();

    boolean isUserAdminTopics();

    boolean isUserModerator();

    boolean isUserAdminDomain();

}