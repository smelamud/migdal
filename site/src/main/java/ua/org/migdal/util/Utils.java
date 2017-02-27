package ua.org.migdal.util;

import java.nio.charset.StandardCharsets;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import java.sql.Timestamp;
import java.util.function.Function;
import javax.xml.bind.DatatypeConverter;

import ua.org.migdal.helper.HelperUtils;

public class Utils {

    public static int random(int min, int max) {
        return (int) (Math.random() * (max - min)) + min;
    }
    
    public static String md5(String s) throws NoSuchAlgorithmException {
        return DatatypeConverter.printHexBinary(
                MessageDigest.getInstance("MD5").digest(s.getBytes(StandardCharsets.UTF_8)));
    }

    public static String ellipsize(String s, int len) {
        if (s.length() <= len) {
            return s;
        }
        String c = s.substring(0, (len - 3) / 2);
        return c + "..." + s.substring(s.length() - (len - 3 - c.length()));
    }

    public static String camelCase(String s) {
        boolean up = true;
        StringBuilder buf = new StringBuilder();
        for (int i = 0; i < s.length(); i++) {
            char c = s.charAt(i);
            if (c == '_') {
                up = true;
            } else {
                buf.append(up ? Character.isUpperCase(c) : c);
                up = false;
            }
        }
        return buf.toString();
    }

    public static String plural(long n, String[] forms) {
        long a = n % 10;
        long b = n / 10 % 10;
        return b == 1 || a >= 5 || a == 0
                ? forms[2]
                : (a == 1 ? forms[0] : forms[1]);
    }

    public static boolean isAsciiNoWhitespace(String s) {
        for (int i = 0; i < s.length(); i++) {
            char c = s.charAt(i);
            if (c <= 32 || c >= 127) {
                return false;
            }
        }
        return true;
    }

    public static boolean isNumber(String s) {
        return s.matches("^\\d+$");
    }

    public static long idOrName(Object value, Function<String, Long> nameToId) {
        if (value == null) {
            return 0;
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
        if (isNumber(valueS)) {
            try {
                return Long.parseLong(valueS);
            } catch (NumberFormatException e) {
            }
        }
        Long id = nameToId.apply(valueS);
        return id != null ? id : 0;
    }

    public static Timestamp now() {
        return new Timestamp(System.currentTimeMillis());
    }

}