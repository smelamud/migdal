package ua.org.migdal.location.exception;

import java.lang.reflect.Method;

public class GeneralViewInvalidReturnTypeException extends RuntimeException {

    public GeneralViewInvalidReturnTypeException(Class<?> controllerClass, Method method) {
        super(String.format("General view location method %s#%s must return LocationInfo",
                controllerClass.getName(), method.getName()));
    }

}
