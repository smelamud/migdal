package daily.coin.form;

import org.hibernate.validator.constraints.Email;
import org.hibernate.validator.constraints.NotBlank;

public class LoginForm extends InterruptingForm {

    @NotBlank
    @Email
    private String email;

    @NotBlank
    private String password;

    public LoginForm() {
    }

    public LoginForm(String back, String defaultBack) {
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

}
