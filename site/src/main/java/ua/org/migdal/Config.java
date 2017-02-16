package ua.org.migdal;

import org.springframework.boot.context.properties.ConfigurationProperties;

@ConfigurationProperties("migdal")
public class Config {

    private String siteDomain;
    private String[] subdomains;
    private int sessionTimeoutShort;
    private int sessionTimeoutLong;

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

}
