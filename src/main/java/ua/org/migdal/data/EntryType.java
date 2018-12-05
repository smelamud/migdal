package ua.org.migdal.data;

import javax.validation.constraints.NotNull;

public enum EntryType {

    NULL,
    POSTING,
    COMMENT,
    TOPIC,
    IMAGE,
    COMPLAIN, // DEPRECATED
    VERSION;

    public static @NotNull EntryType valueOf(int n) {
        if (n < 0 || n >= values().length) {
            return NULL;
        }
        return values()[n];
    }

}