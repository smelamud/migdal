package ua.org.migdal.form;

import java.io.Serializable;

public class ModerateForm implements Serializable {

    private static final long serialVersionUID = 1816429136868790890L;

    private long id;
    private boolean hide;

    public long getId() {
        return id;
    }

    public void setId(long id) {
        this.id = id;
    }

    public boolean isHide() {
        return hide;
    }

    public void setHide(boolean hide) {
        this.hide = hide;
    }

}