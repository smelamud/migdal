package ua.org.migdal.data;

public enum UserRight {

    MIGDAL_STUDENT(0x0001),
    ADMIN_USERS(0x0008),
    ADMIN_TOPICS(0x0010),
    MODERATOR(0x0040),
    ADMIN_DOMAIN(0x0100);

    private int value;

    UserRight(int value) {
        this.value = value;
    }

    public int getValue() {
        return value;
    }

}
