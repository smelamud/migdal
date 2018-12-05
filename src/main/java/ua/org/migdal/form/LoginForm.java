package ua.org.migdal.form;

import java.io.Serializable;
import javax.validation.constraints.NotBlank;
import javax.validation.constraints.Size;

public class LoginForm implements Serializable{

    private static final long serialVersionUID = 23343297276290154L;

    @NotBlank
    @Size(max = 30)
    private String login = "";

    @NotBlank
    @Size(max = 40)
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