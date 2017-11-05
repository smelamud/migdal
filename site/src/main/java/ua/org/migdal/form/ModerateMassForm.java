package ua.org.migdal.form;

import java.io.Serializable;
import java.util.Map;

import ua.org.migdal.data.Posting;
import ua.org.migdal.data.PostingModbit;
import ua.org.migdal.util.Utils;

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

    private boolean isSet(long id, PostingModbit modbit) {
        long[] bits = modbits.get(id);
        return bits == null || Utils.contains(bits, modbit.getValue());
    }

    public boolean isHidden(long id) {
        return isSet(id, PostingModbit.DELETE);
    }

    public boolean isDisabled(long id) {
        return isSet(id, PostingModbit.SPAM);
    }

    public boolean isDelete(long id) {
        return isSet(id, PostingModbit.DELETE);
    }

    public boolean isSpam(long id) {
        return isSet(id, PostingModbit.SPAM);
    }

    public void toPosting(Posting posting) {
        long[] bits = modbits.get(posting.getId());
        if (bits == null) {
            return;
        }
        posting.setHidden(isHidden(posting.getId()));
        posting.setDisabled(isDisabled(posting.getId()));
        posting.setModbits(Utils.disjunct(bits));
    }

}