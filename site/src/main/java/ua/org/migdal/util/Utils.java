package ua.org.migdal.util;

import java.nio.charset.StandardCharsets;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import java.sql.Timestamp;
import javax.xml.bind.DatatypeConverter;

public class Utils {

    public static String md5(String s) throws NoSuchAlgorithmException {
        return DatatypeConverter.printHexBinary(
                MessageDigest.getInstance("MD5").digest(s.getBytes(StandardCharsets.UTF_8)));
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

    public static Timestamp now() {
        return new Timestamp(System.currentTimeMillis());
    }

}