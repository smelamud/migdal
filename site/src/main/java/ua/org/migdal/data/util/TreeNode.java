package ua.org.migdal.data.util;

import java.util.ArrayList;
import java.util.Comparator;
import java.util.List;

import ua.org.migdal.data.TreeElement;

public class TreeNode<T extends TreeElement> {

    private long id;
    private T element;
    private TreeNode<T> parent;
    private List<TreeNode<T>> children = new ArrayList<>();

    public TreeNode() {
    }

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

    protected TreeNode<T> put(T element, long[] ids) {
        int index = 0;
        if (getId() != 0) {
            while (index < ids.length && ids[index] != getId()) {
                index++;
            }
            if (index >= ids.length) { // The element doesn't belong to this subtree
                return null;
            }
            index++;
        }
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
        return node.put(element, ids);
    }

    public void sort(Comparator<? super T> c) {
        children.sort((node1, node2) -> c.compare(node1.getElement(), node2.getElement()));
        for (TreeNode<T> child : children) {
            child.sort(c);
        }
    }

}