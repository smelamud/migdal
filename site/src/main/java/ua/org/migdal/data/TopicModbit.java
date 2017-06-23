package ua.org.migdal.data;

public enum TopicModbit {

    PREMODERATE(0x0001, "Премодерировать"),
    MODERATE(0x0002, "Модерировать"),
    EDIT(0x0004, "Редактировать"),
    ROOT(0x0008, "Корневая"),
    TRANSPARENT(0x0010, "Прозрачная");

    private long value;
    private String description;

    TopicModbit(long value, String description) {
        this.value = value;
        this.description = description;
    }

    public long getValue() {
        return value;
    }

    public String getDescription() {
        return description;
    }

}