package ua.org.migdal;

public interface Session {

    boolean isLoggedIn();

    long getUserId();

    void setUserId(long userId);

    String getDisplayName();

    void setDisplayName(String displayName);

}
