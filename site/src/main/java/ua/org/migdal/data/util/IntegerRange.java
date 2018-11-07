package ua.org.migdal.data.util;

import java.util.Iterator;

public class IntegerRange implements Iterable<Long> {

    private Iterator<Long> iterator;

    public IntegerRange(long begin, long end) {
        iterator = new IntegerRangeIterator(begin, end);
    }

    @Override
    public Iterator<Long> iterator() {
        return iterator;
    }

}
