package ua.org.migdal.data.util;

import java.util.Collection;

import ua.org.migdal.data.TreeElement;
import ua.org.migdal.util.TrackUtils;

public class Tree<T extends TreeElement> extends TreeNode<T> {

    public Tree() {
    }

    public Tree(Collection<T> elements) {
        putAll(elements);
    }

    public TreeNode<T> put(T element) {
        return put(element, TrackUtils.parse(element.getTrack()), 0);
    }

    public void putAll(Collection<T> elements) {
        for (T element : elements) {
            put(element);
        }
    }

}