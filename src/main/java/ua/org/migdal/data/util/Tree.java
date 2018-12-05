package ua.org.migdal.data.util;

import ua.org.migdal.util.TrackUtils;

public class Tree<T extends TreeElement> extends TreeNode<T> {

    public Tree() {
    }

    public Tree(long id) {
        super(id);
    }

    public Tree(Iterable<T> elements) {
        putAll(elements);
    }

    public Tree(long id, Iterable<T> elements) {
        super(id);
        putAll(elements);
    }

    public TreeNode<T> put(T element) {
        return put(element, TrackUtils.parse(element.getTrack()));
    }

    public void putAll(Iterable<T> elements) {
        for (T element : elements) {
            put(element);
        }
    }

}