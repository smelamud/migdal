package ua.org.migdal.data;

public enum EntryType {

    NULL,
    POSTING,
    FORUM,
    TOPIC,
    IMAGE,
    COMPLAIN, // DEPRECATED
    VERSION;

    public static EntryType valueOf(int n) {
        if (n < 0 || n >= values().length) {
            return null;
        }
        return values()[n];
    }

}