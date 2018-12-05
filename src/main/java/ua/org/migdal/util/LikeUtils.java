package ua.org.migdal.util;

public class LikeUtils {

    public static final char ESCAPE_CHAR = '\\';

    public static String escape(String s) {
        return s.replace("%", ESCAPE_CHAR + "%");
    }

    public static String startsWith(String s) {
        return escape(s) + '%';
    }

    public static String endsWith(String s) {
        return '%' + escape(s);
    }

    public static String contains(String s) {
        return '%' + escape(s) + '%';
    }

}