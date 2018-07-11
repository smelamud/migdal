package ua.org.migdal.data;

public class ImagePlacement {

    public static final short LEFT = 1;
    public static final short HCENTER = 2;
    public static final short RIGHT = 3;
    public static final short HORIZONTAL = 3;
    public static final short TOP = 4;
    public static final short VCENTER = 8;
    public static final short BOTTOM = 12;
    public static final short VERTICAL = 12;

    public static final short TOPLEFT = TOP | LEFT;
    public static final short TOPCENTER = TOP | HCENTER;
    public static final short TOPRIGHT = TOP | RIGHT;
    public static final short CENTERLEFT = VCENTER | LEFT;
    public static final short CENTER = VCENTER | HCENTER;
    public static final short CENTERRIGHT = VCENTER | RIGHT;
    public static final short BOTTOMLEFT = BOTTOM | LEFT;
    public static final short BOTTOMCENTER = BOTTOM | HCENTER;
    public static final short BOTTOMRIGHT = BOTTOM | RIGHT;

    public static boolean isPlaced(short placement, short place) {
        int hplace = place & ImagePlacement.HORIZONTAL;
        boolean h = hplace == 0 || (placement & ImagePlacement.HORIZONTAL) == hplace;
        int vplace = place & ImagePlacement.VERTICAL;
        boolean v = vplace == 0 || (placement & ImagePlacement.VERTICAL) == vplace;
        return h && v;
    }

}