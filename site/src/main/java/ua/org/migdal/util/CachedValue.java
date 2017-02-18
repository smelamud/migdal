package ua.org.migdal.util;

import java.util.concurrent.locks.ReadWriteLock;
import java.util.concurrent.locks.ReentrantReadWriteLock;
import java.util.function.Supplier;

public class CachedValue<T> {

    private volatile T value;
    private volatile boolean valid;
    private final ReadWriteLock rwLock = new ReentrantReadWriteLock();
    private Supplier<T> supplier;

    public CachedValue(Supplier<T> supplier) {
        this.supplier = supplier;
    }

    public T get() {
        T result;

        rwLock.readLock().lock();
        if (!valid) {
            // Must release read lock before acquiring write lock
            rwLock.readLock().unlock();
            rwLock.writeLock().lock();
            try {
                // Recheck state because another thread might have
                // acquired write lock and changed state before we did.
                if (!valid) {
                    value = supplier.get();
                    valid = true;
                }
                // Downgrade by acquiring read lock before releasing write lock
                rwLock.readLock().lock();
            } finally {
                rwLock.writeLock().unlock(); // Unlock write, still hold read
            }
        }

        try {
            result = value;
        } finally {
            rwLock.readLock().unlock();
        }

        return result;
    }

}
