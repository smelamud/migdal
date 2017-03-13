package ua.org.migdal.helper.util;

public class Constant {

    private CharSequence title;
    private Object value;

    public Constant() {
    }

    public Constant(CharSequence title, Object value) {
        this.title = title;
        this.value = value;
    }

    public CharSequence getTitle() {
        return title;
    }

    public void setTitle(CharSequence title) {
        this.title = title;
    }

    public Object getValue() {
        return value;
    }

    public void setValue(Object value) {
        this.value = value;
    }

}