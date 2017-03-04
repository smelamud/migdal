package ua.org.migdal.form.validator;

import java.time.DateTimeException;
import java.time.LocalDate;

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
        int year = 1984;
        int month = 1;
        int day = 1;
        try {
            if (!StringUtils.isEmpty(userForm.getBirthYear())) {
                year = Integer.parseInt(userForm.getBirthYear());
            }
            if (userForm.getBirthMonth() != 0) {
                month = userForm.getBirthMonth();
            }
            if (!StringUtils.isEmpty(userForm.getBirthDay())) {
                day = Integer.parseInt(userForm.getBirthDay());
            }
            LocalDate.of(year, month, day);
        } catch (NumberFormatException | DateTimeException e) {
            return "birthdayInvalid";
        }
        return null;
    }

}
