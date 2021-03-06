package ua.org.migdal.util;

import java.nio.charset.StandardCharsets;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import java.sql.Timestamp;
import java.time.LocalDateTime;
import java.util.ArrayList;
import java.util.Collection;
import java.util.List;
import java.util.function.Function;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

import javax.xml.bind.DatatypeConverter;

import com.ibm.icu.util.Calendar;
import com.ibm.icu.util.GregorianCalendar;
import com.ibm.icu.util.HebrewCalendar;

public class Utils {

    public static int random(int min, int max) {
        return (int) (Math.random() * (max - min)) + min;
    }

    // Only for small values of len
    public static int[] randomArray(int len, int min, int max) {
        if (max - min + 1 < len) {
            throw new IllegalArgumentException("Impossible to fill the array that long with numbers in the range");
        }

        int[] n = new int[len];
        int i = 0;
        while (i < len) {
            int m = Utils.random(min, max);
            for (int j = 0; ; j++) {
                if (j < i && n[j] == m) {
                    break;
                }
                if (j == i) {
                    n[i++] = m;
                    break;
                }
            }
        }
        return n;
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
                buf.append(up ? Character.toUpperCase(c) : Character.toLowerCase(c));
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

    public static boolean isAsciiNoWhitespaceHtmlSafe(String s) {
        for (int i = 0; i < s.length(); i++) {
            char c = s.charAt(i);
            if (c <= 32 || c >= 127 || c == '<' || c == '>' | c == '&') {
                return false;
            }
        }
        return true;
    }

    public static boolean isNumber(String s) {
        return s.matches("^\\d+$");
    }

    public static Long toLong(String s, Long defaultValue) {
        try {
            return Long.valueOf(s);
        } catch (NumberFormatException e) {
            return defaultValue;
        }
    }

    public static Long toLong(String s) {
        return toLong(s, null);
    }

    public static Short toShort(String s, Short defaultValue) {
        try {
            return Short.valueOf(s);
        } catch (NumberFormatException e) {
            return defaultValue;
        }
    }

    public static Short toShort(String s) {
        return toShort(s, null);
    }

    public static String replaceAll(String text, Pattern regex, Function<Matcher, String> replacement) {
        Matcher matcher = regex.matcher(text);
        StringBuffer buf = new StringBuffer();
        while (matcher.find()) {
            matcher.appendReplacement(buf, replacement.apply(matcher));
        }
        matcher.appendTail(buf);
        return buf.toString();
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

    public static void copyToCalendar(LocalDateTime dateTime, Calendar calendar) {
        calendar.set(dateTime.getYear(), dateTime.getMonthValue() - 1, dateTime.getDayOfMonth(),
                dateTime.getHour(), dateTime.getMinute(), dateTime.getSecond());
    }

    public static HebrewCalendar toHebrew(GregorianCalendar gregorianCalendar) {
        HebrewCalendar hebrewCalendar = new HebrewCalendar();
        hebrewCalendar.setTime(gregorianCalendar.getTime());
        hebrewCalendar.setTimeZone(gregorianCalendar.getTimeZone());
        return hebrewCalendar;
    }

    public static boolean contains(long[] array, long value) {
        for (long x : array) {
            if (x == value) {
                return true;
            }
        }
        return false;
    }

    public static long disjunct(long[] array) {
        long value = 0;
        for (long x : array) {
            if (x > 0) { // negative means special values
                value |= x;
            }
        }
        return value;
    }

    public static long[] toArray(Collection<Long> list) {
        long[] array = new long[list.size()];
        int i = 0;
        for (Long x : list) {
            array[i++] = x;
        }
        return array;
    }

    public static List<Long> toList(long[] array) {
        List<Long> list = new ArrayList<>(array.length);
        for (long x : array) {
            list.add(x);
        }
        return list;
    }

    public static String toPathName(String constName) {
        return constName.replace('_', '-').toLowerCase();
    }

    public static String toConstName(String pathName) {
        return pathName.replace('-', '_').toUpperCase();
    }

}