package ua.org.migdal.form;

import javax.validation.constraints.Size;

import org.hibernate.validator.constraints.NotBlank;

public class LoginForm {

    @NotBlank
    @Size(max=30)
    private String login = "";

    @NotBlank
    @Size(max=40)
    private String password = "";

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