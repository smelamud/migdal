package ua.org.migdal.util;

import java.util.HashMap;
import java.util.Map;

public class MimeUtils {

    private static final Map<String, String> MIME_EXTENSIONS = new HashMap<>();
    private static final Map<String, String> MIME_TYPES = new HashMap<>();
    private static final Map<String, String> IMAGE_TYPES = new HashMap<>();

    static {
        MIME_EXTENSIONS.put("image/pjpeg", "jpg");
        MIME_EXTENSIONS.put("image/jpeg", "jpg");
        MIME_EXTENSIONS.put("image/gif", "gif");
        MIME_EXTENSIONS.put("image/x-png", "png");
        MIME_EXTENSIONS.put("image/png", "png");
        MIME_EXTENSIONS.put("application/zip", "zip");

        MIME_TYPES.put("jpg", "image/jpeg");
        MIME_TYPES.put("gif", "image/gif");
        MIME_TYPES.put("png", "image/png");
        MIME_TYPES.put("zip", "application/zip");
        
        IMAGE_TYPES.put("image/pjpeg", "jpeg");
        IMAGE_TYPES.put("image/jpeg", "jpeg");
        IMAGE_TYPES.put("image/gif", "gif");
        IMAGE_TYPES.put("image/x-png", "png");
        IMAGE_TYPES.put("image/png", "png");
    }

    public static String extension(String mimeType) {
        return MIME_EXTENSIONS.getOrDefault(mimeType, "");
    }

    public static String mimeType(String extension) {
        return MIME_TYPES.getOrDefault(extension, "");
    }

    public static boolean isImage(String mimeType) {
        return IMAGE_TYPES.containsKey(mimeType);
    }

    public static String imageFormat(String mimeType) {
        return IMAGE_TYPES.get(mimeType);
    }

}