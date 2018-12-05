package ua.org.migdal.data.api;

public class LoginExistence {

    private String login;
    private boolean exists;

    public LoginExistence(String login, boolean exists) {
        this.login = login;
        this.exists = exists;
    }

    public String getLogin() {
        return login;
    }

    public boolean isExists() {
        return exists;
    }

}