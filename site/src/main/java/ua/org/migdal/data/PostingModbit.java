package ua.org.migdal.data;

public enum PostingModbit implements Modbit {

    MODERATE(0x0001, 'M', "Модерировать"),
    @Deprecated HTML(0x0002, 'H', "HTML"),
    EDIT(0x0004, 'E', "Редактировать"),
    ATTENTION(0x0008, 'S', "Особо проверить"),
    MULTIPART(0x0010, 'L', "Многостраничное"),

    /* Special values */
    HIDDEN(-1),
    DISABLED(-2),
    DELETE(-3),
    SPAM(-4);

    private long value;
    private char letter;
    private String description;

    PostingModbit(long value) {
        this.value = value;
    }

    PostingModbit(long value, char letter, String description) {
        this.value = value;
        this.letter = letter;
        this.description = description;
    }

    @Override
    public long getValue() {
        return value;
    }

    public boolean isSpecial() {
        return value < 0;
    }

    public char getLetter() {
        return letter;
    }

    public String getDescription() {
        return description;
    }

}