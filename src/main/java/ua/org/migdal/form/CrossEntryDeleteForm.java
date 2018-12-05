package ua.org.migdal.form;

import java.io.Serializable;

public class CrossEntryDeleteForm implements Serializable {

    private static final long serialVersionUID = -7169697552371965353L;

    private long id;

    public long getId() {
        return id;
    }

    public void setId(long id) {
        this.id = id;
    }

}
