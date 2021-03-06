package ua.org.migdal.util;

import java.util.Arrays;

import org.springframework.util.StringUtils;

public class TrackUtils {

    public static String track(long id) {
        return track(id, null);
    }

    public static String track(long id, String upTrack) {
        StringBuilder buf = new StringBuilder();
        if (!StringUtils.isEmpty(upTrack)) {
            buf.append(upTrack);
            buf.append(' ');
        }
        buf.append(String.format("%010d", id));
        return buf.toString();
    }

    public static String track(long[] ids) {
        return track(ids, null);
    }

    public static String track(long[] ids, String upTrack) {
        StringBuilder buf = new StringBuilder();
        if (!StringUtils.isEmpty(upTrack)) {
            buf.append(upTrack);
            if (ids != null && ids.length > 0) {
                buf.append(' ');
            }
        }
        if (ids != null) {
            for (int i = 0; i < ids.length; i++) {
                if (i != 0) {
                    buf.append(' ');
                }
                buf.append(String.format("%010d", ids[i]));
            }
        }
        return buf.toString();
    }

    public static long[] parse(String track) {
        if (StringUtils.isEmpty(track)) {
            return new long[0];
        }

        String[] items = track.split(" ");
        long[] ids = new long[items.length];
        for (int i = 0; i < items.length; i++) {
            ids[i] = Long.parseLong(items[i]);
        }
        return ids;
    }

    public static String parent(String track) {
        long[] ids = parse(track);
        return track(Arrays.copyOf(ids, ids.length - 1));
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