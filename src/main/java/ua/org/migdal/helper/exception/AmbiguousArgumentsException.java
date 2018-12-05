package ua.org.migdal.helper.exception;

public class AmbiguousArgumentsException extends RuntimeException {

    public AmbiguousArgumentsException(String paramName1, String paramName2) {
        super(getMessageText(paramName1, paramName2));
    }

    public AmbiguousArgumentsException(String paramName1, String paramName2, Throwable cause) {
        super(getMessageText(paramName1, paramName2), cause);
    }

    private static String getMessageText(String paramName1, String paramName2) {
        return String.format("Ambiguous parameters: both '%s' and '%s' present", paramName1, paramName2);
    }

}