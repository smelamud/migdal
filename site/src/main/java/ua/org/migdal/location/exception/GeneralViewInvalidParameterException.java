package ua.org.migdal.location.exception;

import java.lang.reflect.Method;

public class GeneralViewInvalidParameterException extends RuntimeException {

    public GeneralViewInvalidParameterException(Class<?> controllerClass, Method method, Class<?> parameterClass) {
        super(String.format("Invalid parameter type %s in general view location method %s#%s",
                parameterClass.getName(), controllerClass.getName(), method.getName()));
    }

}
