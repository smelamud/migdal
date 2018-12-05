package ua.org.migdal.data;

import java.util.Arrays;
import java.util.stream.Collectors;

import ua.org.migdal.util.Utils;

public enum TopicModbit implements Modbit {

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

    @Override
    public long getValue() {
        return value;
    }

    public String getDescription() {
        return description;
    }

    public int getBit() {
        return ordinal();
    }

    public boolean isSet(long modbits) {
        return (modbits & getValue()) != 0;
    }

    public long unset(long modbits) {
        return modbits & ~getValue();
    }

    public static long[] parse(long modbits) {
        return Utils.toArray(Arrays.stream(values())
                .map(TopicModbit::getValue)
                .filter(value -> (modbits & value) != 0)
                .collect(Collectors.toList()));
    }
    
}