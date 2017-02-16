package ua.org.migdal.controller;

import java.util.concurrent.Callable;

import org.springframework.validation.Errors;

public class ControllerAction {

    private Errors errors;

    public ControllerAction(Errors errors) {
        this.errors = errors;
    }

    public void execute(Callable<String> callable) {
        if (errors.hasErrors()) {
            return;
        }
        try {
            String errorCode = callable.call();
            if (errorCode != null && !errorCode.isEmpty()) {
                errors.reject(errorCode);
            }
        } catch (Exception e) {
            errors.reject("internal-failure");
        }
    }

}
