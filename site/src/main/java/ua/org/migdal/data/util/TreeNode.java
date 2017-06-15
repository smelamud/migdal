package ua.org.migdal.data.util;

import java.util.ArrayList;
import java.util.List;

import ua.org.migdal.data.TreeElement;

public class TreeNode<T extends TreeElement> {

    private long id;
    private T element;
    private TreeNode<T> parent;
    private List<TreeNode<T>> children = new ArrayList<>();

    public TreeNode(long id) {
        this.id = id;
    }

    public TreeNode(long id, TreeNode<T> parent) {
        this.id = id;
        this.parent = parent;
    }

    public long getId() {
        return id;
    }

    public void setId(long id) {
        this.id = id;
    }

    public T getElement() {
        return element;
    }

    public void setElement(T element) {
        this.element = element;
    }

    public TreeNode<T> getParent() {
        return parent;
    }

    public List<TreeNode<T>> getChildren() {
        return children;
    }

    protected TreeNode<T> put(T element, long[] ids, int index) {
        if (index >= ids.length) {
            setElement(element);
            return this;
        }
        TreeNode<T> node = null;
        for (TreeNode<T> child : children) {
            if (child.getId() == ids[index]) {
                node = child;
                break;
            }
        }
        if (node == null) {
            node = new TreeNode<T>(ids[index], this);
            children.add(node);
        }
        return node.put(element, ids, index + 1);
    }

}