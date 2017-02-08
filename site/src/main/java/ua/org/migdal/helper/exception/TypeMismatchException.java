package ua.org.migdal.helper.exception;

public class TypeMismatchException extends RuntimeException {

    public TypeMismatchException(String paramName, String typeName, String value) {
        super(getMessageText(paramName, typeName, value));
    }

    public TypeMismatchException(String paramName, Class<?> cls, String value) {
        this(paramName, cls.getName(), value);
    }

    public TypeMismatchException(String paramName, String typeName, String value, Throwable cause) {
        super(getMessageText(paramName, typeName, value), cause);
    }

    public TypeMismatchException(String paramName, Class<?> cls, String value, Throwable cause) {
        this(paramName, cls.getName(), value, cause);
    }

    private static String getMessageText(String paramName, String typeName, String value) {
        return String.format("Incorrect value for type %s passed as parameter '%s': %s", typeName, paramName, value);
    }

}
