package ua.org.migdal.form.validator;

import org.springframework.validation.Errors;

import ua.org.migdal.form.SignupForm;

@FormValidator
public class SignupFormValidator extends AbstractFormValidator<SignupForm> {

    protected SignupFormValidator() {
        super(SignupForm.class);
    }

    @Override
    public void validateForm(SignupForm signupForm, Errors errors) {
        if (!signupForm.getPassword().equals(signupForm.getRetypePassword())) {
            errors.reject("passwordsDifferent");
        }
    }

}
