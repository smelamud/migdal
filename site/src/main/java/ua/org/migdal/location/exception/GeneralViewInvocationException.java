package ua.org.migdal.location.exception;

import java.lang.reflect.Method;

public class GeneralViewInvocationException extends RuntimeException {

    public GeneralViewInvocationException(Class<?> controllerClass, Method method, Throwable cause) {
        super(String.format("Exception thrown during invocation of general view location method %s#%s: %s",
                controllerClass.getName(), method.getName(), cause.getMessage()), cause);
    }

}
