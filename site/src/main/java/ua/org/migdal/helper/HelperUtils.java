package ua.org.migdal.helper;

import ua.org.migdal.helper.exception.TypeMismatchException;

public class HelperUtils {

    public static Integer integerArgument(String paramName, String value) {
        if (value == null) {
            return null;
        }
        if (value.isEmpty()) {
            return 0;
        }
        try {
            return Integer.parseInt(value);
        } catch (NumberFormatException e) {
            throw new TypeMismatchException(paramName, "int", value);
        }
    }

    public static int intArgument(String paramName, String value) {
        Integer intValue = integerArgument(paramName, value);
        return intValue != null ? intValue : 0;
    }

    public static Integer integerArgument(int paramN, String value) {
        return integerArgument(Integer.toString(paramN), value);
    }

    public static int intArgument(int paramN, String value) {
        return intArgument(Integer.toString(paramN), value);
    }

}
