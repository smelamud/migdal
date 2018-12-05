package ua.org.migdal.form;

import java.io.Serializable;

public class TopicDeleteForm implements Serializable {

    private long id;
    private long destId;

    public TopicDeleteForm() {
    }

    public TopicDeleteForm(long id) {
        this.id = id;
    }

    public long getId() {
        return id;
    }

    public void setId(long id) {
        this.id = id;
    }

    public long getDestId() {
        return destId;
    }

    public void setDestId(long destId) {
        this.destId = destId;
    }

}