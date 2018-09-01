package ua.org.migdal.form;

import java.io.Serializable;

public class InnerImageDeleteForm implements Serializable {

    private static final long serialVersionUID = 867899830606930048L;

    private long id;

    public long getId() {
        return id;
    }

    public void setId(long id) {
        this.id = id;
    }

}