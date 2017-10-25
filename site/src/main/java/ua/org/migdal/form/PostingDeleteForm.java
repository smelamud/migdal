package ua.org.migdal.form;

import java.io.Serializable;

public class PostingDeleteForm implements Serializable {

    private static final long serialVersionUID = 2351298282079362475L;

    private long id;

    public long getId() {
        return id;
    }

    public void setId(long id) {
        this.id = id;
    }

}