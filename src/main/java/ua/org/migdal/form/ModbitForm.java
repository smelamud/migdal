package ua.org.migdal.form;

import java.io.Serializable;
import java.util.Arrays;
import java.util.List;
import java.util.stream.Collectors;

import ua.org.migdal.data.Posting;
import ua.org.migdal.data.PostingModbit;
import ua.org.migdal.data.util.Selected;
import ua.org.migdal.util.Utils;

public class ModbitForm implements Serializable {

    private static final long serialVersionUID = 1876497445086961698L;

    private long id;

    private long[] modbits = new long[0];

    public ModbitForm() {
    }

    public ModbitForm(Posting posting) {
        if (posting == null) {
            return;
        }

        id = posting.getId();
        modbits = PostingModbit.parse(posting.getModbits(), posting.isHidden(), posting.isDisabled());
    }

    public long getId() {
        return id;
    }

    public void setId(long id) {
        this.id = id;
    }

    public long[] getModbits() {
        return modbits;
    }

    public void setModbits(long[] modbits) {
        this.modbits = modbits;
    }

    public List<Selected<PostingModbit>> getModbitsSelection() {
        return Arrays.stream(PostingModbit.values())
                .filter(bit -> !bit.isSpecial())
                .map(bit -> new Selected<>(bit, Utils.contains(modbits, bit.getValue())))
                .collect(Collectors.toList());
    }

    public boolean isHidden() {
        return Utils.contains(modbits, PostingModbit.HIDDEN.getValue());
    }

    public boolean isDisabled() {
        return Utils.contains(modbits, PostingModbit.DISABLED.getValue());
    }

    public void toPosting(Posting posting) {
        posting.setHidden(isHidden());
        posting.setDisabled(isDisabled());
        posting.setModbits(Utils.disjunct(getModbits()));
    }

}