package ua.org.migdal.form;

import java.io.Serializable;

import ua.org.migdal.data.PostingModbit;

public class AdminModeratorForm implements Serializable {

    private static final long serialVersionUID = 6483801443202853111L;

    private int bit = PostingModbit.MODERATE.ordinal();

    private boolean asc;

    public int getBit() {
        return bit;
    }

    public void setBit(int bit) {
        this.bit = bit;
    }

    public boolean isAsc() {
        return asc;
    }

    public void setAsc(boolean asc) {
        this.asc = asc;
    }

}