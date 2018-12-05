package ua.org.migdal.grp;

public enum ThumbnailTransformFlag {

    /**
     * Thumbnail is not needed.
     */
    NONE,

    /**
     * Thumbnail is created automatically by resizing.
     */
    AUTO,

    /**
     * Thumbnail is created automatically by cropping.
     */
    CLIP;

    public static ThumbnailTransformFlag parse(String name) {
        for (ThumbnailTransformFlag flag : values()) {
            if (flag.name().equalsIgnoreCase(name)) {
                return flag;
            }
        }
        return null;
    }

}