package ua.org.migdal.data.util;

import ua.org.migdal.data.TreeElement;
import ua.org.migdal.util.TrackUtils;

public class Tree<T extends TreeElement> extends TreeNode<T> {

    public Tree() {
    }

    public Tree(Iterable<T> elements) {
        putAll(elements);
    }

    public TreeNode<T> put(T element) {
        return put(element, TrackUtils.parse(element.getTrack()), 0);
    }

    public void putAll(Iterable<T> elements) {
        for (T element : elements) {
            put(element);
        }
    }

}