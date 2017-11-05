package ua.org.migdal.form;

import java.io.Serializable;
import java.util.Map;

public class ModerateMassForm implements Serializable {

    private long[] ids;

    private Map<Long, long[]> modbits;

    public ModerateMassForm() {
    }

    public long[] getIds() {
        return ids;
    }

    public void setIds(long[] ids) {
        this.ids = ids;
    }

    public Map<Long, long[]> getModbits() {
        return modbits;
    }

    public void setModbits(Map<Long, long[]> modbits) {
        this.modbits = modbits;
    }

}