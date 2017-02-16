package ua.org.migdal.session;

public interface Session {

    long getUserId();

    void setUserId(long userId);

    long getRealUserId();

    void setRealUserId(long realUserId);

    /**
     * Get timestamp (in milliseconds from the beginning of the UNIX epoch of the last access to the session.
     */
    long getLast();

    void setLast(long last);

    /**
     * Get maximum duration of the session (in hours).
     */
    int getDuration();

    void setDuration(int duration);

}
