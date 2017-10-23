package ua.org.migdal.form;

import java.io.Serializable;

public class RenewForm implements Serializable {

    private static final long serialVersionUID = 1816429136868790890L;

    private long id;

    public long getId() {
        return id;
    }

    public void setId(long id) {
        this.id = id;
    }

}