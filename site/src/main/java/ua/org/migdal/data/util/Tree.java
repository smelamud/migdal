package ua.org.migdal.data.util;

import java.util.ArrayList;
import java.util.List;

import ua.org.migdal.data.TreeElement;
import ua.org.migdal.util.TrackUtils;

public class Tree<T extends TreeElement> {

    private List<TreeNode<T>> children = new ArrayList<>();

    public List<TreeNode<T>> getChildren() {
        return children;
    }

    public TreeNode<T> insert(T element) {
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
        return node.insert(element, ids, 1);
    }

}