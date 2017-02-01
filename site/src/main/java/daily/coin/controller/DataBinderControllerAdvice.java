package daily.coin.controller;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.context.ApplicationContext;
import org.springframework.validation.DefaultMessageCodesResolver;
import org.springframework.validation.Validator;
import org.springframework.web.bind.WebDataBinder;
import org.springframework.web.bind.annotation.ControllerAdvice;
import org.springframework.web.bind.annotation.InitBinder;

import daily.coin.form.validator.FormValidator;

@ControllerAdvice
public class DataBinderControllerAdvice {

    @Autowired
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
