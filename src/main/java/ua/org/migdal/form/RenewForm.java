package ua.org.migdal.form;

import java.io.Serializable;

public class RenewForm implements Serializable {

    private static final long serialVersionUID = -2330054322526656637L;

    private long id;

    public long getId() {
        return id;
    }

    public void setId(long id) {
        this.id = id;
    }

}