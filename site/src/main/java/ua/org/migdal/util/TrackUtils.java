package ua.org.migdal.util;

public class TrackUtils {

    public static long[] parse(String track) {
        String[] items = track.split(" ");
        long[] ids = new long[items.length];
        for (int i = 0; i < items.length; i++) {
            ids[i] = Long.parseLong(items[i]);
        }
        return ids;
    }

    public static String toPath(String track) {
        return toPath(track, 0);
    }

    public static String toPath(String track, int upLevels) {
        long[] ids = parse(track);
        StringBuilder buf = new StringBuilder();
        for (int i = 0; i < ids.length - upLevels; i++) {
            buf.append(ids[i]);
            buf.append('/');
        }
        return buf.toString();
    }

}