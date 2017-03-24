package ua.org.migdal.data;

import java.util.ArrayList;
import java.util.List;

public enum UserRight {

    MIGDAL_STUDENT(0x0001, false),
    ADMIN_USERS(0x0008, true),
    ADMIN_TOPICS(0x0010, true),
    MODERATOR(0x0040, true),
    ADMIN_DOMAIN(0x0100, true);

    private long value;
    private boolean admin;

    UserRight(long value, boolean admin) {
        this.value = value;
        this.admin = admin;
    }

    public long getValue() {
        return value;
    }

    public boolean isAdmin() {
        return admin;
    }

    public static UserRight findByValue(long value) {
        for (UserRight userRight : values()) {
            if (userRight.getValue() == value) {
                return userRight;
            }
        }
        return null;
    }

    public static long collect(long[] rights) {
        long result = 0;
        if (rights != null) {
            for (long right : rights) {
                result |= right;
            }
        }
        return result;
    }

    public static long[] parse(long rights) {
        List<UserRight> result = new ArrayList<>();
        for (UserRight right : values()) {
            if ((rights & right.getValue()) != 0) {
                result.add(right);
            }
        }
        return result.stream().mapToLong(UserRight::getValue).toArray();
    }

}
