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
    private boolean disableRegister;
    private int regConfirmTimeout;
    private int inplaceSize;
    private int inplaceSizeMinus;
    private int inplaceSizePlus;

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

    public boolean isDisableRegister() {
        return disableRegister;
    }

    public void setDisableRegister(boolean disableRegister) {
        this.disableRegister = disableRegister;
    }

    public int getRegConfirmTimeout() {
        return regConfirmTimeout;
    }

    public void setRegConfirmTimeout(int regConfirmTimeout) {
        this.regConfirmTimeout = regConfirmTimeout;
    }

    public int getInplaceSize() {
        return inplaceSize;
    }

    public void setInplaceSize(int inplaceSize) {
        this.inplaceSize = inplaceSize;
    }

    public int getInplaceSizeMinus() {
        return inplaceSizeMinus;
    }

    public void setInplaceSizeMinus(int inplaceSizeMinus) {
        this.inplaceSizeMinus = inplaceSizeMinus;
    }

    public int getInplaceSizePlus() {
        return inplaceSizePlus;
    }

    public void setInplaceSizePlus(int inplaceSizePlus) {
        this.inplaceSizePlus = inplaceSizePlus;
    }

}