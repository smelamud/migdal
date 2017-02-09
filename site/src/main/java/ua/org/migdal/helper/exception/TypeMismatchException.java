package ua.org.migdal.helper.exception;

public class TypeMismatchException extends RuntimeException {

    private String paramName;
    private String typeName;
    private String value;

    public String getParamName() {
        return paramName;
    }

    public String getTypeName() {
        return typeName;
    }

    public String getValue() {
        return value;
    }

    public TypeMismatchException(String paramName, String typeName, String value) {
        this.paramName = paramName;
        this.typeName = typeName;
        this.value = value;
    }

    public TypeMismatchException(String paramName, Class<?> cls, String value) {
        this(paramName, cls.getName(), value);
    }

    public TypeMismatchException(String paramName, String typeName, String value, Throwable cause) {
        super(cause);
        this.paramName = paramName;
        this.typeName = typeName;
        this.value = value;
    }

    public TypeMismatchException(String paramName, Class<?> cls, String value, Throwable cause) {
        this(paramName, cls.getName(), value, cause);
    }

    public TypeMismatchException(int paramN, String typeName, String value) {
        this(Integer.toString(paramN), typeName, value);
    }

    public TypeMismatchException(int paramN, Class<?> cls, String value) {
        this(Integer.toString(paramN), cls, value);
    }

    public TypeMismatchException(int paramN, String typeName, String value, Throwable cause) {
        this(Integer.toString(paramN), typeName, value, cause);
    }

    public TypeMismatchException(int paramN, Class<?> cls, String value, Throwable cause) {
        this(Integer.toString(paramN), cls, value, cause);
    }

    @Override
    public String getMessage() {
        return String.format("Incorrect value for type %s passed as parameter '%s': %s", typeName, paramName, value);
    }

}
