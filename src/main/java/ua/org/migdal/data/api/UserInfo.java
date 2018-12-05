package ua.org.migdal.data.api;

public class UserInfo {

    private String login;
    private String fullName;
    private String rank;
    private boolean femine;
    private long lastOnline;

    public UserInfo(String login, String fullName, String rank, boolean femine, long lastOnline) {
        this.login = login;
        this.fullName = fullName;
        this.rank = rank;
        this.femine = femine;
        this.lastOnline = lastOnline;
    }

    public String getLogin() {
        return login;
    }

    public void setLogin(String login) {
        this.login = login;
    }

    public String getFullName() {
        return fullName;
    }

    public void setFullName(String fullName) {
        this.fullName = fullName;
    }

    public String getRank() {
        return rank;
    }

    public void setRank(String rank) {
        this.rank = rank;
    }

    public boolean isFemine() {
        return femine;
    }

    public void setFemine(boolean femine) {
        this.femine = femine;
    }

    public long getLastOnline() {
        return lastOnline;
    }

    public void setLastOnline(long lastOnline) {
        this.lastOnline = lastOnline;
    }

}