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
        if (!StringUtils.isEmpty(userForm.getNewPassword())) {
            if (!userForm.getNewPassword().equals(userForm.getDupPassword())) {
                return "passwordsDifferent";
            }
            if (userForm.getNewPassword().length() < 6) {
                return "newPassword.length";
            }
        }
        int year = 1984;
        int month = 1;
        int day = 1;
        try {
            if (!StringUtils.isEmpty(userForm.getBirthYear())) {
                int value = Integer.parseInt(userForm.getBirthYear());
                if (value != 0) {
                    year = value;
                }
            }
            if (userForm.getBirthMonth() != 0) {
                month = userForm.getBirthMonth();
            }
            if (!StringUtils.isEmpty(userForm.getBirthDay())) {
                int value = Integer.parseInt(userForm.getBirthDay());
                if (value != 0) {
                    day = value;
                }
            }
            LocalDate.of(year, month, day);
        } catch (NumberFormatException | DateTimeException e) {
            return "birthdayInvalid";
        }
        return null;
    }

}
