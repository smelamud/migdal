package ua.org.migdal.form;

import java.io.Serializable;
import javax.validation.constraints.Size;

import org.hibernate.validator.constraints.NotBlank;

public class SuForm implements Serializable {

    private static final long serialVersionUID = -1726574299878551362L;

    @NotBlank
    @Size(max=30)
    private String login = "";

    public SuForm() {
    }

    public String getLogin() {
        return login;
    }

    public void setLogin(String login) {
        this.login = login;
    }

}
