package ua.org.migdal.data.util;

public class Siblings<T> {

    private Iterable<T> list;
    private boolean moreBefore;
    private boolean moreAfter;

    public Siblings(Iterable<T> list, boolean moreBefore, boolean moreAfter) {
        this.list = list;
        this.moreBefore = moreBefore;
        this.moreAfter = moreAfter;
    }

    public Iterable<T> getList() {
        return list;
    }

    public boolean isMoreBefore() {
        return moreBefore;
    }

    public boolean isMoreAfter() {
        return moreAfter;
    }

}
