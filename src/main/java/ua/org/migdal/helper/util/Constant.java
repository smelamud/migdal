package ua.org.migdal.helper.util;

public class Constant<T> {

    private CharSequence title;
    private T value;

    public Constant() {
    }

    public Constant(CharSequence title, T value) {
        this.title = title;
        this.value = value;
    }

    public CharSequence getTitle() {
        return title;
    }

    public void setTitle(CharSequence title) {
        this.title = title;
    }

    public T getValue() {
        return value;
    }

    public void setValue(T value) {
        this.value = value;
    }

    public static <T> boolean hasValue(Constant<? extends T>[] constants, T value) {
        for (Constant constant : constants) {
            if (constant.getValue().equals(value)) {
                return true;
            }
        }
        return false;
    }

}