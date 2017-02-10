package ua.org.migdal.form;

import ua.org.migdal.data.User;
import org.hibernate.validator.constraints.Email;
import org.hibernate.validator.constraints.NotBlank;

public class SignupForm extends InterruptingForm {

    @NotBlank
    @Email
    private String email;

    @NotBlank
    private String password;

    @NotBlank
    private String retypePassword;

    public SignupForm() {
    }

    public SignupForm(String back, String defaultBack) {
        super(back, defaultBack);
    }

    public String getEmail() {
        return email;
    }

    public void setEmail(String email) {
        this.email = email;
    }

    public String getPassword() {
        return password;
    }

    public void setPassword(String password) {
        this.password = password;
    }

    public String getRetypePassword() {
        return retypePassword;
    }

    public void setRetypePassword(String retypePassword) {
        this.retypePassword = retypePassword;
    }

    public void toUser(User user) {
//        user.setEmail(email);
        user.setPassword(password);
    }

}
