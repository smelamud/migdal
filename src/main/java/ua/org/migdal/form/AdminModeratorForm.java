package ua.org.migdal.form;

import java.io.Serializable;

import ua.org.migdal.data.PostingModbit;

public class AdminModeratorForm implements Serializable {

    private static final long serialVersionUID = 6483801443202853111L;

    private long bit = PostingModbit.MODERATE.getValue();

    private boolean asc;

    public long getBit() {
        return bit;
    }

    public void setBit(long bit) {
        this.bit = bit;
    }

    public boolean isAsc() {
        return asc;
    }

    public void setAsc(boolean asc) {
        this.asc = asc;
    }

}