package ua.org.migdal.data;

public enum LinkType {

    NONE,
    SEE_ALSO,
    MAJOR,
    PUBLISH;

    public static LinkType valueOf(int value) {
        if (value < 0 || value >= values().length) {
            return null;
        }
        return values()[value];
    }

}