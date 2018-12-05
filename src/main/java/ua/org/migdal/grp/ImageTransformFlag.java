package ua.org.migdal.grp;

public enum ImageTransformFlag {

    /**
     * Image is uploaded by user.
     */
    MANUAL,

    /**
     * Image is uploaded by user and resized automatically.
     */
    RESIZE;

    public static ImageTransformFlag parse(String name) {
        for (ImageTransformFlag flag : values()) {
            if (flag.name().equalsIgnoreCase(name)) {
                return flag;
            }
        }
        return null;
    }

}