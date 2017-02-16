package ua.org.migdal.controller;

import java.util.concurrent.Callable;

import javax.persistence.PersistenceException;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.validation.Errors;

public class ControllerAction {

    private Logger log;
    private String actionName;
    private Errors errors;

    public ControllerAction(Class<?> cls, String actionName, Errors errors) {
        log = LoggerFactory.getLogger(cls);
        this.actionName = actionName;
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
        } catch (PersistenceException e) {
            log.error("{}: database failure: {}", actionName, e.getMessage());
            if (e.getCause() != null) {
                log.error("{}: caused by: {}", actionName, e.getCause().getMessage());
            }
            errors.reject("persistence-failure");
        } catch (Exception e) {
            log.error("{}: internal error: {}", actionName, e.getMessage());
            log.error("Exception:", e);
            errors.reject("internal-failure");
        }
    }

}
