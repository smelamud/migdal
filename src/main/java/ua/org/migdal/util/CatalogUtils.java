package ua.org.migdal.util;

import org.springframework.util.StringUtils;

import ua.org.migdal.data.EntryType;
import ua.org.migdal.data.TopicModbit;
import ua.org.migdal.util.UriUtils.Slash;

public class CatalogUtils {

    private static String catalog(long id, String ident, String upCatalog) {
        if (!StringUtils.isEmpty(ident)) {
            if (ident.startsWith("post.")) {
                ident = ident.substring(5);
            }
            int pos = ident.indexOf(',');
            if (pos >= 0) {
                ident = ident.substring(0, pos);
            }
            String path = ident.replace('.', '/');
            return normalize(path);
        } else {
            if (StringUtils.isEmpty(upCatalog)) {
                return String.format("%d/", id);
            }
            upCatalog = normalize(upCatalog);
            return String.format("%s%d/", upCatalog, id);
        }
    }

    private static String catalog(long id, String ident) {
        return catalog(id, ident, "");
    }

    public static String catalog(EntryType entryType, long id, String ident, long modbits, String upCatalog) {
        if (entryType == EntryType.TOPIC && TopicModbit.ROOT.isSet(modbits) && TopicModbit.TRANSPARENT.isSet(modbits)) {
            return "";
        } else if (entryType == EntryType.TOPIC && TopicModbit.ROOT.isSet(modbits)) {
            return catalog(id, ident);
        } else if (entryType == EntryType.TOPIC && TopicModbit.TRANSPARENT.isSet(modbits)) {
            return upCatalog;
        } else {
            return catalog(id, ident, upCatalog);
        }
    }

    public static int length(String catalog) {
        if (catalog == null) {
            return 0;
        }

        int len = 0;
        for (int i = 0; i < catalog.length(); i++) {
            if (catalog.charAt(i) == '/') {
                len++;
            }
        }
        return len;
    }

    public static String normalize(String catalog) {
        return !StringUtils.isEmpty(catalog) ? UriUtils.normalizePath(catalog, true, Slash.NO, Slash.YES) : "";
    }

    public static String sub(String catalog, int start, int length) {
        if (length == 0 || StringUtils.isEmpty(catalog)) {
            return "";
        }
        String[] elements = catalog.substring(0, catalog.length() - 1).split("/");
        StringBuilder buf = new StringBuilder();
        int begin = start >= 0 ? start : elements.length + start;
        int end = length > 0 ? begin + length : elements.length + length;
        for (int i = begin; i < end; i++) {
            buf.append(elements[i]);
            buf.append('/');
        }
        return buf.toString();
    }

    public static String element(String catalog, int n) {
        if (StringUtils.isEmpty(catalog)) {
            return null;
        }
        String[] elements = catalog.substring(0, catalog.length() - 1).split("/");
        if (n < 0 || n >= elements.length) {
            return null;
        }
        return elements[n];
    }

    public static String toIdOrIdent(String catalog) {
        if (StringUtils.isEmpty(catalog)) {
            return "0";
        }
        String last = sub(catalog, -1, 1);
        last = last.substring(0, last.length() - 1);
        return Utils.isNumber(last) ? last : catalog.substring(0, catalog.length() - 1).replace('/', '.');
    }

    public static String toIdent(String catalog) {
        if (StringUtils.isEmpty(catalog)) {
            return "0";
        }
        return catalog.substring(0, catalog.length() - 1).replace('/', '.');
    }

}