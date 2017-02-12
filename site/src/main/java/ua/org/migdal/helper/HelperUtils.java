package ua.org.migdal.helper;

import com.github.jknack.handlebars.Options;
import ua.org.migdal.helper.exception.MissingArgumentException;
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

    public static void appendHashParam(StringBuilder buf, String name, Options options) {
        appendHashParam(buf, name, name, options);
    }

    public static void appendHashParam(StringBuilder buf, String name, String attrName, Options options) {
        appendAttribute(buf, attrName, options.hash(name));
    }

    public static void appendHashParam(StringBuilder buf, String name, String attrName, String defaultValue,
                                       Options options) {
        String value = defaultValue != null ? options.hash(name, defaultValue) : options.hash(name);
        if (value == null) {
            throw new MissingArgumentException(name);
        }
        appendAttribute(buf, attrName, value);
    }

    public static void appendAttribute(StringBuilder buf, String attributeName, Object value) {
        if (value != null) {
            buf.append(' ');
            buf.append(attributeName);
            buf.append("=\"");
            buf.append(value);
            buf.append('"');
        }
    }

    public static String mandatoryHash(String name, Options options) {
        String value = options.hash(name);
        if (value == null) {
            throw new MissingArgumentException(name);
        }
        return value;
    }

}
