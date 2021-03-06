package ua.org.migdal.session;

import java.util.List;
import java.util.Set;

import ua.org.migdal.data.User;

public interface RequestContext {

    void temporarySession(User user, long realUserId);

    String getLocation();

    String getCatalog();

    String getCatalog(int start, int length);

    String getCatalog(int start);

    String getCatalogElement(int n);

    int getCatalogLength();

    String getSubdomain();

    int getPort();

    String getSiteUrl();

    String getSiteUrl(String subdomain);

    boolean isWww();

    boolean isEnglish();

    String getWwwSiteUrl();

    String getEnglishSiteUrl();

    String getIp();

    String getBack();

    boolean isHasBack();

    String getOrigin();

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

    List<String> getOgImages();

    void addOgImage(String src);

}
