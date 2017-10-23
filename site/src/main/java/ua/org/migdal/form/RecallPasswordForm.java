package ua.org.migdal.form;

import java.io.Serializable;
import javax.validation.constraints.NotBlank;
import javax.validation.constraints.Size;

public class RecallPasswordForm implements Serializable {

    private static final long serialVersionUID = 7193305452391657545L;

    @NotBlank
    @Size(max=30)
    private String login = "";

    public RecallPasswordForm() {
    }

    public String getLogin() {
        return login;
    }

    public void setLogin(String login) {
        this.login = login;
    }

}