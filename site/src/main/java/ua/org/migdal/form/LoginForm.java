package ua.org.migdal.form;

import org.hibernate.validator.constraints.NotBlank;

public class LoginForm {

    @NotBlank
    private String login;

    @NotBlank
    private String password;

    private boolean myComputer = true;

    public LoginForm() {
    }

    public String getLogin() {
        return login;
    }

    public void setLogin(String login) {
        this.login = login;
    }

    public String getPassword() {
        return password;
    }

    public void setPassword(String password) {
        this.password = password;
    }

    public boolean isMyComputer() {
        return myComputer;
    }

    public void setMyComputer(boolean myComputer) {
        this.myComputer = myComputer;
    }

}