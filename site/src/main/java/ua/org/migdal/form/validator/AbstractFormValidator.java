package ua.org.migdal.form.validator;

import org.springframework.validation.Errors;
import org.springframework.validation.Validator;

public abstract class AbstractFormValidator<T> implements Validator {

    private Class<T> formClass;

    protected AbstractFormValidator(Class<T> formClass) {
        this.formClass = formClass;
    }

    @Override
    public boolean supports(Class<?> aClass) {
        return true;
    }

    @Override
    public void validate(Object o, Errors errors) {
        if (errors.hasErrors() || !formClass.isInstance(o)) {
            return;
        }

        @SuppressWarnings("unchecked")
        T form = (T) o;
        String errorCode = validateForm(form);
        if (errorCode != null) {
            errors.reject(errorCode);
        }
    }

    public abstract String validateForm(T form);

}