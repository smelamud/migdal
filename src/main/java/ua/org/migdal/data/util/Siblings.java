package ua.org.migdal.data.util;

import java.util.Iterator;

public class Siblings<T> implements Iterable<T> {

    private Iterable<T> list;
    private boolean moreBefore;
    private boolean moreAfter;

    public Siblings(Iterable<T> list, boolean moreBefore, boolean moreAfter) {
        this.list = list;
        this.moreBefore = moreBefore;
        this.moreAfter = moreAfter;
    }

    public boolean isMoreBefore() {
        return moreBefore;
    }

    public boolean isMoreAfter() {
        return moreAfter;
    }

    @Override
    public Iterator<T> iterator() {
        return list.iterator();
    }

}
