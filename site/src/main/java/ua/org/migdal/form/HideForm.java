package ua.org.migdal.form;

import java.io.Serializable;

public class HideForm implements Serializable {

    private static final long serialVersionUID = 8209971206905463074L;

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