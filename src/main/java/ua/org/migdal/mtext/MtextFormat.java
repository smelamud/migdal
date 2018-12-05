package ua.org.migdal.mtext;

public enum MtextFormat {

    LINE,
    SHORT,
    LONG;

    public boolean lessThan(MtextFormat peer) {
        return ordinal() < peer.ordinal();
    }

    public boolean greaterThan(MtextFormat peer) {
        return ordinal() > peer.ordinal();
    }

    public boolean atLeast(MtextFormat peer) {
        return ordinal() >= peer.ordinal();
    }

}