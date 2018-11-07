package ua.org.migdal.data.util;

import java.util.Iterator;
import java.util.NoSuchElementException;

public class IntegerRangeIterator implements Iterator<Long> {

    private long end;
    private long current;
    private long step;

    public IntegerRangeIterator(long begin, long end) {
        this.end = end;
        current = begin;
        step = begin <= end ? 1 : -1;
    }

    @Override
    public boolean hasNext() {
        return step > 0 ? current < end : current > end;
    }

    @Override
    public Long next() {
        if (!hasNext()) {
            throw new NoSuchElementException();
        }
        long result = current;
        current += step;
        return result;
    }

}
