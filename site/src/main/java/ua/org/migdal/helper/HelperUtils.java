package ua.org.migdal.helper;

import com.github.jknack.handlebars.Options;
import ua.org.migdal.helper.exception.MissingArgumentException;
import ua.org.migdal.helper.exception.TypeMismatchException;

public class HelperUtils {

    public static Long integerArg(String paramName, Object value) {
        if (value == null) {
            return null;
        }
        if (value instanceof Short) {
            return ((Short) value).longValue();
        }
        if (value instanceof Integer) {
            return ((Integer) value).longValue();
        }
        if (value instanceof Long) {
            return (Long) value;
        }
        String valueS = value.toString();
        if (valueS.isEmpty()) {
            return 0L;
        }
        try {
            return Long.parseLong(valueS);
        } catch (NumberFormatException e) {
            throw new TypeMismatchException(paramName, "integer", valueS);
        }
    }

    public static long intArg(String paramName, Object value) {
        Long intValue = integerArg(paramName, value);
        return intValue != null ? intValue : 0;
    }

    public static Long integerArg(int paramN, Object value) {
        return integerArg(Integer.toString(paramN), value);
    }

    public static long intArg(int paramN, Object value) {
        return intArg(Integer.toString(paramN), value);
    }

    public static Boolean booleanArg(Object value) {
        if (value == null) {
            return null;
        }
        if (value instanceof Boolean) {
            return (Boolean) value;
        }
        String valueS = value.toString();
        if (valueS.isEmpty()) {
            return false;
        }
        if (valueS.equals("0")) {
            return false;
        }
        if (valueS.equals("1")) {
            return true;
        }
        return Boolean.parseBoolean(valueS);
    }

    public static boolean boolArg(Object value) {
        Boolean valueB = booleanArg(value);
        return valueB != null ? valueB : false;
    }

    public static void appendOptionalArgAttr(StringBuilder buf, String name, Options options) {
        appendOptionalArgAttr(buf, name, name, options);
    }

    public static void appendOptionalArgAttr(StringBuilder buf, String name, String attrName, Options options) {
        appendAttr(buf, attrName, options.hash(name));
    }

    public static void appendMandatoryArgAttr(StringBuilder buf, String name, Options options) {
        appendMandatoryArgAttr(buf, name, name, options);
    }

    public static void appendMandatoryArgAttr(StringBuilder buf, String name, String attrName, Options options) {
        appendArgAttr(buf, name, attrName, (String) null, options);
    }

    public static void appendArgAttr(StringBuilder buf, String name, String defaultValue, Options options) {
        appendArgAttr(buf, name, name, defaultValue, options);
    }

    public static void appendArgAttr(StringBuilder buf, String name, String attrName, String defaultValue,
                                     Options options) {
        String value = defaultValue != null ? options.hash(name, defaultValue) : options.hash(name);
        if (value == null) {
            throw new MissingArgumentException(name);
        }
        appendAttr(buf, attrName, value);
    }

    public static void appendArgAttr(StringBuilder buf, String name, Boolean defaultValue, Options options) {
        appendArgAttr(buf, name, name, defaultValue, options);
    }

    public static void appendArgAttr(StringBuilder buf, String name, String attrName, Boolean defaultValue,
                                     Options options) {
        Object value = defaultValue != null ? options.hash(name, defaultValue) : options.hash(name);
        if (value == null) {
            throw new MissingArgumentException(name);
        }
        appendAttr(buf, attrName, boolArg(value));
    }

    public static void appendAttr(StringBuilder buf, String attrName, Object value) {
        if (value != null) {
            buf.append(' ');
            buf.append(attrName);
            buf.append("=\"");
            buf.append(value);
            buf.append('"');
        }
    }

    public static void appendAttr(StringBuilder buf, String attrName, boolean value) {
        if (value) {
            buf.append(' ');
            buf.append(attrName);
        }
    }

    public static <T> T mandatoryHash(String name, Options options) {
        T value = options.hash(name);
        if (value == null) {
            throw new MissingArgumentException(name);
        }
        return value;
    }

}
