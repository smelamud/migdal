package ua.org.migdal.util;

import org.springframework.util.StringUtils;

public class UriUtils {

    public enum Slash {
        ANY, YES, NO
    }

    public static String normalizePath(String path, boolean singleSlash, Slash firstSlash, Slash lastSlash) {
        if (StringUtils.isEmpty(path)) {
            return firstSlash == Slash.YES || lastSlash == Slash.YES ? "/" : "";
        }
        if (singleSlash) {
            path = path.replaceAll("/+", "/");
        }
        if (firstSlash == Slash.YES && !path.startsWith("/")) {
            path = "/" + path;
        }
        if (firstSlash == Slash.NO && path.startsWith("/")) {
            path = path.substring(1);
        }
        if (lastSlash == Slash.YES && !path.endsWith("/")) {
            path += "/";
        }
        if (lastSlash == Slash.NO && path.endsWith("/")) {
            path = path.substring(0, path.length() - 1);
        }
        return path;
    }

    public static String normalizePath(String path) {
        return normalizePath(path, false, Slash.ANY, Slash.ANY);
    }

}