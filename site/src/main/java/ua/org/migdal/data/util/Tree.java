package ua.org.migdal.data.util;

import java.util.ArrayList;
import java.util.Collection;
import java.util.List;

import ua.org.migdal.data.TreeElement;
import ua.org.migdal.util.TrackUtils;

public class Tree<T extends TreeElement> {

    private List<TreeNode<T>> children = new ArrayList<>();

    public Tree() {
    }

    public Tree(Collection<T> elements) {
        putAll(elements);
    }

    public TreeNode<T> getParent() {
        return null;
    }

    public List<TreeNode<T>> getChildren() {
        return children;
    }

    public TreeNode<T> put(T element) {
        long[] ids = TrackUtils.parse(element.getTrack());
        TreeNode<T> node = null;
        for (TreeNode<T> child : children) {
            if (child.getId() == ids[0]) {
                node = child;
                break;
            }
        }
        if (node == null) {
            node = new TreeNode<T>(ids[0]);
            children.add(node);
        }
        return node.put(element, ids, 1);
    }

    public void putAll(Collection<T> elements) {
        for (T element : elements) {
            put(element);
        }
    }

}