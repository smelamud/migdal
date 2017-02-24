package ua.org.migdal.data;

public class ImagePlacement {

    public final static short IPL_LEFT = 1;
    public final static short IPL_HCENTER = 2;
    public final static short IPL_RIGHT = 3;
    public final static short IPL_HORIZONTAL = 3;
    public final static short IPL_TOP = 4;
    public final static short IPL_VCENTER = 8;
    public final static short IPL_BOTTOM = 12;
    public final static short IPL_VERTICAL = 12;

    public final static short IPL_TOPLEFT = IPL_TOP | IPL_LEFT;
    public final static short IPL_TOPCENTER = IPL_TOP | IPL_HCENTER;
    public final static short IPL_TOPRIGHT = IPL_TOP | IPL_RIGHT;
    public final static short IPL_CENTERLEFT = IPL_VCENTER | IPL_LEFT;
    public final static short IPL_CENTER = IPL_VCENTER | IPL_HCENTER;
    public final static short IPL_CENTERRIGHT = IPL_VCENTER | IPL_RIGHT;
    public final static short IPL_BOTTOMLEFT = IPL_BOTTOM | IPL_LEFT;
    public final static short IPL_BOTTOMCENTER = IPL_BOTTOM | IPL_HCENTER;
    public final static short IPL_BOTTOMRIGHT = IPL_BOTTOM | IPL_RIGHT;

}