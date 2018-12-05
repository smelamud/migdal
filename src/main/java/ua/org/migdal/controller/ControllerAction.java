package ua.org.migdal.controller;

import java.util.HashMap;
import java.util.Map;
import java.util.concurrent.Callable;

import javax.persistence.PersistenceException;

import org.hibernate.exception.ConstraintViolationException;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.dao.DataAccessException;
import org.springframework.transaction.PlatformTransactionManager;
import org.springframework.transaction.TransactionStatus;
import org.springframework.transaction.support.DefaultTransactionDefinition;
import org.springframework.validation.Errors;
import ua.org.migdal.imageupload.ImageUploadException;

public class ControllerAction {

    private Logger log;
    private String actionName;
    private Errors errors;

    private PlatformTransactionManager txManager;

    private Map<String, String> constraints = new HashMap<>();

    public ControllerAction(Class<?> cls, String actionName, Errors errors) {
        log = LoggerFactory.getLogger(cls);
        this.actionName = actionName;
        this.errors = errors;
    }

    public ControllerAction transactional(PlatformTransactionManager txManager) {
        this.txManager = txManager;
        return this;
    }

    public ControllerAction constraint(String constraintName, String errorCode) {
        constraints.put(constraintName, errorCode);
        return this;
    }

    public void execute(Callable<String> callable) {
        if (errors.hasErrors()) {
            return;
        }
        try {
            String errorCode = executeCallable(callable);
            if (errorCode != null && !errorCode.isEmpty()) {
                errors.reject(errorCode);
            }
        } catch (ImageUploadException e) {
            errors.reject(e.getFieldErrorCode());
        } catch (PersistenceException | DataAccessException e) {
            log.error("{}: database failure: {}", actionName, e.getMessage());
            if (e.getCause() != null) {
                if (e.getCause() instanceof ConstraintViolationException) {
                    ConstraintViolationException cve = (ConstraintViolationException) e.getCause();
                    String errorCode = constraints.get(cve.getConstraintName());
                    if (errorCode != null) {
                        errors.reject(errorCode);
                        return;
                    }
                }
                log.error("{}: caused by: {}", actionName, e.getCause().getMessage());
            }
            errors.reject("persistence-failure");
        } catch (Exception e) {
            log.error("{}: internal error: {}", actionName, e.getMessage());
            log.error("Exception:", e);
            errors.reject("internal-failure");
        }
    }

    private TransactionStatus beginTransaction() {
        return txManager != null ? txManager.getTransaction(new DefaultTransactionDefinition()) : null;
    }

    private void commitTransaction(TransactionStatus status) {
        if (status != null) {
            txManager.commit(status);
        }
    }

    private void rollbackTransaction(TransactionStatus status) {
        if (status != null) {
            txManager.rollback(status);
        }
    }

    private String executeCallable(Callable<String> callable) throws Exception {
        TransactionStatus status = beginTransaction();
        String errorCode = null;
        try {
            errorCode = callable.call();
        } catch (Exception e) {
            rollbackTransaction(status);
            throw e;
        }
        if (errorCode != null && !errorCode.isEmpty()) {
            rollbackTransaction(status);
        } else {
            commitTransaction(status);
        }
        return errorCode;
    }

}
