package ua.org.migdal.data.util;

public class Selected<T> {

    private T value;
    private boolean selected;

    public Selected(T value, boolean selected) {
        this.value = value;
        this.selected = selected;
    }

    public T getValue() {
        return value;
    }

    public void setValue(T value) {
        this.value = value;
    }

    public boolean isSelected() {
        return selected;
    }

    public void setSelected(boolean selected) {
        this.selected = selected;
    }

}