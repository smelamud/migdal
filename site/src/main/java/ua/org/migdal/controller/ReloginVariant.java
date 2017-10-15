package ua.org.migdal.controller;

public enum ReloginVariant {

    NONE(0),
    GUEST(1),
    SAME(2),
    LOGIN(3);

    private int value;

    ReloginVariant(int value) {
        this.value = value;
    }

    public int getValue() {
        return value;
    }

    public static ReloginVariant valueOf(int value) {
        for (ReloginVariant variant : values()) {
            if (variant.getValue() == value) {
                return variant;
            }
        }
        return null;
    }

}