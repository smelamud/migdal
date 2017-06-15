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

}