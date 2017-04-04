package ua.org.migdal.text;

public enum TextFormat {

    PLAIN(0),
    TEX(1),
    XML(2),
    MAIL(3);

    private short value;

    TextFormat(int value) {
        this.value = (short) value;
    }

    public short getValue() {
        return value;
    }

}