package ua.org.migdal.util;

import javax.servlet.http.HttpServletRequest;

import org.springframework.util.StringUtils;
import org.springframework.web.util.UriComponentsBuilder;

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

    public static String getUrlDomain(String url) {
        try {
            String host = UriComponentsBuilder.fromUriString(url).build().getHost().toLowerCase();
            if (host.startsWith("www.")) {
                host = host.substring(4);
            }
            return host;
        } catch (Exception e) {
            return "";
        }
    }

    public static UriComponentsBuilder createBuilderFromRequest(HttpServletRequest request) {
        return UriComponentsBuilder
                .fromHttpUrl(request.getRequestURL().toString())
                .query(request.getQueryString());
    }

    public static UriComponentsBuilder createLocalBuilderFromRequest(HttpServletRequest request) {
        return UriComponentsBuilder
                .fromPath(request.getRequestURI())
                .query(request.getQueryString());
    }

}
