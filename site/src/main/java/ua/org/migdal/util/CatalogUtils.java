package ua.org.migdal.util;

import org.springframework.util.StringUtils;

import ua.org.migdal.data.EntryType;
import ua.org.migdal.data.TopicModbit;
import ua.org.migdal.util.UriUtils.Slash;

public class CatalogUtils {

    public static String catalog(long id, String ident, String upCatalog) {
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
            if (StringUtils.isEmpty(upCatalog)) {
                return String.format("%d/", id);
            }
            upCatalog = UriUtils.normalizePath(upCatalog, false, Slash.NO, Slash.YES);
            return String.format("%s%d/", upCatalog, id);
        }
    }

    public static String catalog(long id, String ident) {
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

}