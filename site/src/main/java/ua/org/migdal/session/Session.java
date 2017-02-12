package ua.org.migdal.session;

public interface Session {

    long getUserId();

    void setUserId(long userId);

    public long getRealUserId();

    public void setRealUserId(long realUserId);

}
