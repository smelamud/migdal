package ua.org.migdal.form.validator;

import org.springframework.util.StringUtils;

import ua.org.migdal.form.UserForm;

@FormValidator
public class UserFormValidator extends AbstractFormValidator<UserForm> {

    protected UserFormValidator() {
        super(UserForm.class);
    }

    @Override
    public String validateForm(UserForm userForm) {
        if (!StringUtils.isEmpty(userForm.getNewPassword())
                && !userForm.getNewPassword().equals(userForm.getDupPassword())) {
            return "passwordsDifferent";
        }
        return null;
    }

}
