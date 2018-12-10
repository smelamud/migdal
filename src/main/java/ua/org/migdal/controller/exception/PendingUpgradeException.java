package ua.org.migdal.controller.exception;

import org.springframework.http.HttpStatus;
import org.springframework.web.bind.annotation.ResponseStatus;

@ResponseStatus(HttpStatus.SERVICE_UNAVAILABLE)
public class PendingUpgradeException extends Exception {

    public PendingUpgradeException() {
        super("Website is being upgraded");
    }

}
