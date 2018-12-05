package ua.org.migdal.util;

public class Perm {

    public static final long USER = 0;
    public static final long GROUP = 4;
    public static final long OTHER = 8;
    public static final long GUEST = 12;

    public static final long READ = 1;
    public static final long WRITE = 2;
    public static final long APPEND = 4;
    public static final long POST = 8;

    public static final long UR = 0x0001;
    public static final long UW = 0x0002;
    public static final long UA = 0x0004;
    public static final long UP = 0x0008;
    public static final long GR = 0x0010;
    public static final long GW = 0x0020;
    public static final long GA = 0x0040;
    public static final long GP = 0x0080;
    public static final long OR = 0x0100;
    public static final long OW = 0x0200;
    public static final long OA = 0x0400;
    public static final long OP = 0x0800;
    public static final long ER = 0x1000;
    public static final long EW = 0x2000;
    public static final long EA = 0x4000;
    public static final long EP = 0x8000;

    public static final long NONE = 0x0000;
    public static final long ALL = 0xFFFF;

}