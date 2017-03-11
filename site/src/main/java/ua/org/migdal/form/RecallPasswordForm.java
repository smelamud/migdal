package ua.org.migdal.form;

import org.hibernate.validator.constraints.NotBlank;

public class RecallPasswordForm {

    @NotBlank
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