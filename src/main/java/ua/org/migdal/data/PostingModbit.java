package ua.org.migdal.data;

import java.util.Arrays;
import java.util.List;
import java.util.stream.Collectors;

import ua.org.migdal.util.Utils;

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

    public String getName() {
        return name();
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

    public int getBit() {
        return ordinal();
    }

    public static PostingModbit valueOf(long value) {
        for (PostingModbit modbit : values()) {
            if (modbit.getValue() == value) {
                return modbit;
            }
        }
        return null;
    }

    public static long[] parse(long modbits, boolean hidden, boolean disabled) {
        List<Long> bitList = Arrays.stream(values())
                .filter(bit -> !bit.isSpecial())
                .map(PostingModbit::getValue)
                .filter(value -> (modbits & value) != 0)
                .collect(Collectors.toList());
        if (hidden) {
            bitList.add(HIDDEN.getValue());
        }
        if (disabled) {
            bitList.add(DISABLED.getValue());
        }
        return Utils.toArray(bitList);
    }

}