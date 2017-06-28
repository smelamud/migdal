package ua.org.migdal.helper;

import javax.inject.Inject;

import com.github.jknack.handlebars.Handlebars.SafeString;
import com.github.jknack.handlebars.Options;

import ua.org.migdal.grp.GrpDescriptor;
import ua.org.migdal.grp.GrpEnum;
import ua.org.migdal.helper.exception.TypeMismatchException;
import ua.org.migdal.helper.util.HelperUtils;
import ua.org.migdal.util.Utils;

@HelperSource
public class GrpHelperSource {

    @Inject
    private FormsHelperSource formsHelperSource;

    @Inject
    private GrpEnum grpEnum;

    public CharSequence grpSelect(Options options) {
        String name = HelperUtils.mandatoryHash("name", options);
        Object value = HelperUtils.mandatoryHash("value", options);
        boolean multiple = HelperUtils.boolArg(options.hash("multiple", false));
        CharSequence rows = options.hash("rows", "10");

        long grpValue = 0;
        long[] grpValues = new long[0];
        if (!multiple) {
            grpValue = HelperUtils.intArg("value", value);
        } else {
            if (value instanceof long[]) {
                grpValues = (long[]) value;
            } else {
                throw new TypeMismatchException("value", "integer[]", value);
            }
        }

        StringBuilder buf = new StringBuilder();
        if (!multiple) {
            buf.append("<select name=\"");
            buf.append(name);
            buf.append("\">");
        } else {
            buf.append("<select name=\"");
            buf.append(name);
            buf.append("\" size=\"");
            buf.append(rows);
            buf.append("\" multiple>");
        }
        for (GrpDescriptor grp : grpEnum.getGrps()) {
            buf.append(formsHelperSource.selectOption(grp.getValue(),
                    multiple && Utils.contains(grpValues, grp.getValue()) || grpValue == grp.getValue(),
                    null, grp.getTitle()));
        }
        buf.append("</select>");
        return new SafeString(buf);
    }

    public CharSequence formGrpSelect(Options options) {
        CharSequence title = HelperUtils.mandatoryHash("title", options);
        boolean mandatory = HelperUtils.boolArg(options.hash("mandatory", false));
        CharSequence comment = options.hash("comment", "");
        String name = HelperUtils.mandatoryHash("name", options);
        Object value = HelperUtils.mandatoryHash("value", options);
        boolean multiple = HelperUtils.boolArg(options.hash("multiple", false));
        CharSequence rows = options.hash("rows", "10");

        long grpValue = 0;
        long[] grpValues = new long[0];
        if (!multiple) {
            grpValue = HelperUtils.intArg("value", value);
        } else {
            if (value instanceof long[]) {
                grpValues = (long[]) value;
            } else {
                throw new TypeMismatchException("value", "integer[]", value);
            }
        }

        StringBuilder buf = new StringBuilder();
        buf.append(formsHelperSource.formSelectBegin(options, title, mandatory, comment, name, "list", multiple, rows));
        for (GrpDescriptor grp : grpEnum.getGrps()) {
            buf.append(formsHelperSource.formOption(options, grp.getTitle(), grp.getValue(),
                    multiple && Utils.contains(grpValues, grp.getValue()) || grpValue == grp.getValue()));
        }
        buf.append(formsHelperSource.formSelectEnd("list"));
        return new SafeString(buf);
    }

}