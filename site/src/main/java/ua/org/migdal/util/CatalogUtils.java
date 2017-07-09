package ua.org.migdal.util;

import org.springframework.util.StringUtils;

import ua.org.migdal.util.UriUtils.Slash;

public class CatalogUtils {

    public static String catalog(long id, String ident, String prev) {
        if (!StringUtils.isEmpty(ident)) {
            if (ident.startsWith("post.")) {
                ident = ident.substring(5);
            }
            int pos = ident.indexOf(',');
            if (pos >= 0) {
                ident = ident.substring(0, pos);
            }
            String path = ident.replace('.', '/');
            return UriUtils.normalizePath(path, false, Slash.NO, Slash.YES);
        } else {
            if (StringUtils.isEmpty(prev)) {
                return String.format("%d/", id);
            }
            prev = UriUtils.normalizePath(prev, false, Slash.NO, Slash.YES);
            return String.format("%s%d/", prev, id);
        }
    }

    public static String catalog(long id, String ident) {
        return catalog(id, ident, "");
    }

}