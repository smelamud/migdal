package ua.org.migdal;

import org.springframework.boot.context.properties.ConfigurationProperties;

@ConfigurationProperties("migdal")
public class Config {

    private String siteDomain;
    private String[] subdomains;
    private int sessionTimeoutShort;
    private int sessionTimeoutLong;
    private boolean allowGuests;
    private String guestLogin;

    public String getSiteDomain() {
        return siteDomain;
    }

    public void setSiteDomain(String siteDomain) {
        this.siteDomain = siteDomain;
    }

    public String[] getSubdomains() {
        return subdomains;
    }

    public void setSubdomains(String[] subdomains) {
        this.subdomains = subdomains;
    }

    public int getSessionTimeoutShort() {
        return sessionTimeoutShort;
    }

    public void setSessionTimeoutShort(int sessionTimeoutShort) {
        this.sessionTimeoutShort = sessionTimeoutShort;
    }

    public int getSessionTimeoutLong() {
        return sessionTimeoutLong;
    }

    public void setSessionTimeoutLong(int sessionTimeoutLong) {
        this.sessionTimeoutLong = sessionTimeoutLong;
    }

    public boolean isAllowGuests() {
        return allowGuests;
    }

    public void setAllowGuests(boolean allowGuests) {
        this.allowGuests = allowGuests;
    }

    public String getGuestLogin() {
        return guestLogin;
    }

    public void setGuestLogin(String guestLogin) {
        this.guestLogin = guestLogin;
    }

}