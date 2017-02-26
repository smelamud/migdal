package ua.org.migdal.data;

public class ImagePlacement {

    public final static short LEFT = 1;
    public final static short HCENTER = 2;
    public final static short RIGHT = 3;
    public final static short HORIZONTAL = 3;
    public final static short TOP = 4;
    public final static short VCENTER = 8;
    public final static short BOTTOM = 12;
    public final static short VERTICAL = 12;

    public final static short TOPLEFT = TOP | LEFT;
    public final static short TOPCENTER = TOP | HCENTER;
    public final static short TOPRIGHT = TOP | RIGHT;
    public final static short CENTERLEFT = VCENTER | LEFT;
    public final static short CENTER = VCENTER | HCENTER;
    public final static short CENTERRIGHT = VCENTER | RIGHT;
    public final static short BOTTOMLEFT = BOTTOM | LEFT;
    public final static short BOTTOMCENTER = BOTTOM | HCENTER;
    public final static short BOTTOMRIGHT = BOTTOM | RIGHT;

}