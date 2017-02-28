package ua.org.migdal.helper.exception;

public class DateFormatException extends TypeMismatchException {

    private String datePattern;

    public DateFormatException(String paramName, String datePattern, Object value) {
        super(paramName, "date", value);
        this.datePattern = datePattern;
    }

    public DateFormatException(int paramN, String datePattern, Object value) {
        this(Integer.toString(paramN), datePattern, value);
    }

    @Override
    public String getMessage() {
        return String.format("Incorrect date passed as parameter '%s' (pattern is %s): %s",
                getParamName(), datePattern, getValue());
    }

}
