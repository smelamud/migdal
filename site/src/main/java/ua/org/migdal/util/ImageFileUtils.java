package ua.org.migdal.util;

import java.util.regex.Matcher;
import java.util.regex.Pattern;

import ua.org.migdal.Config;

public class ImageFileUtils {

    private static final Pattern FILENAME = Pattern.compile("^.*/migdal-(\\d+).(\\w+)$");

    public static String imageFilename(String format, long fileId) {
        return String.format("migdal-%d.%s", fileId, MimeUtils.extension(format));
    }

    public ImageFileInfo parseFilename(String filename) {
        Matcher m = FILENAME.matcher(filename);
        if (!m.matches()) {
            return null;
        }
        try {
            return new ImageFileInfo(m.group(2), MimeUtils.mimeType(m.group(2)), Long.valueOf(m.group(1)));
        } catch (NumberFormatException e) {
            return null;
        }
    }

    public static String imagePath(String format, long fileId) {
        return String.format("%s/%s", Config.getInstance().getImageDir(), imageFilename(format, fileId));
    }

    public static String imageUrl(String format, long fileId) {
        String imageUrl = Config.getInstance().getImageUrl();
        String filename = imageFilename(format, fileId);
        if (!imageUrl.startsWith("/")) {
            return String.format("/%s/%s", imageUrl, filename);
        } else {
            return String.format("%s/%s", imageUrl, filename);
        }
    }

}