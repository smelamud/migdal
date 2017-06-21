package ua.org.migdal.controller;

import javax.inject.Inject;
import org.springframework.context.ApplicationContext;
import org.springframework.validation.DefaultMessageCodesResolver;
import org.springframework.validation.Validator;
import org.springframework.web.bind.WebDataBinder;
import org.springframework.web.bind.annotation.ControllerAdvice;
import org.springframework.web.bind.annotation.InitBinder;

import ua.org.migdal.form.validator.FormValidator;

@ControllerAdvice
public class DataBinderControllerAdvice {

    @Inject
    private ApplicationContext applicationContext;

    @InitBinder
    public void initBinder(WebDataBinder binder) {
        DefaultMessageCodesResolver messageCodesResolver = new DefaultMessageCodesResolver();
        messageCodesResolver.setMessageCodeFormatter(DefaultMessageCodesResolver.Format.POSTFIX_ERROR_CODE);
        binder.setMessageCodesResolver(messageCodesResolver);

        binder.addValidators(
                applicationContext.getBeansWithAnnotation(FormValidator.class).values().toArray(new Validator[0]));
    }

}
