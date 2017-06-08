package ua.org.migdal.form;

import javax.validation.constraints.Size;

import org.hibernate.validator.constraints.NotBlank;

public class SuForm {

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
