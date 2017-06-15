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
        long[] ids = parse(track);
        StringBuilder buf = new StringBuilder();
        for (long id : ids) {
            buf.append(id);
            buf.append('/');
        }
        return buf.toString();
    }

}