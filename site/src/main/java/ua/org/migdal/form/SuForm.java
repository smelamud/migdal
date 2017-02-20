package ua.org.migdal.form;

import org.hibernate.validator.constraints.NotBlank;

public class SuForm {

    @NotBlank
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
