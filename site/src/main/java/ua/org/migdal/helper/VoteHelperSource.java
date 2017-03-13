package ua.org.migdal.helper;

import com.github.jknack.handlebars.Handlebars.SafeString;
import com.github.jknack.handlebars.Options;
import ua.org.migdal.helper.util.HelperUtils;

@HelperSource
public class VoteHelperSource {

    public CharSequence rating(Options options) {
        long value = HelperUtils.intArg("value", options.hash("value"));
        CharSequence style = options.hash("style");
        Object id = options.hash("id", "0");

        return rating(value, style, id);
    }

    CharSequence rating(long value) {
        return rating(value, null, "0");
    }

    CharSequence rating(long value, CharSequence style, Object id) {
        StringBuilder buf = new StringBuilder();
        buf.append("<span class=\"small-rating-");
        HelperUtils.safeAppend(buf, id);
        buf.append(' ');
        if (value == 0) {
            buf.append("small-rating-zero");
        } else if (value > 0) {
            buf.append("small-rating-plus");
        } else {
            buf.append("small-rating-minus");
        }
        buf.append('"');
        if (style != null && style.length() > 0) {
            buf.append(" style=\"");
            buf.append(style);
            buf.append('"');
        }
        buf.append(">(");
        if (value > 0) {
            buf.append('+');
        }
        buf.append(value);
        buf.append(")</span>");
        return new SafeString(buf);
    }

}