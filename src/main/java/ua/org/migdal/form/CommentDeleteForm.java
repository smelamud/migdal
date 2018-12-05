package ua.org.migdal.form;

import java.io.Serializable;

public class CommentDeleteForm implements Serializable {

    private static final long serialVersionUID = 2659793328156026035L;

    private long id;

    public long getId() {
        return id;
    }

    public void setId(long id) {
        this.id = id;
    }

}