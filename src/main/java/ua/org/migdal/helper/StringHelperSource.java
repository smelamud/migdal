package ua.org.migdal.helper;

import java.io.UnsupportedEncodingException;
import java.net.URLEncoder;

import com.github.jknack.handlebars.Handlebars.SafeString;

import ua.org.migdal.helper.util.HelperUtils;
import ua.org.migdal.util.Utils;

@HelperSource
public class StringHelperSource {

    public CharSequence ue(Object s) {
        try {
            return URLEncoder.encode(s.toString(), "UTF-8");
        } catch (UnsupportedEncodingException e) {
            return "ue:" + e.getMessage();
        }
    }

    public CharSequence safe(String s) {
        return new SafeString(s);
    }

    public CharSequence asp(Object s) {
        boolean safe = s instanceof SafeString;
        String str = s.toString();
        str = str != null
                ? str.replaceAll(">\\s+", ">").replaceAll("\\s+<", "<")
                : null;
        return safe ? new SafeString(str) : str;
    }

    public CharSequence plural(Object n, String forms) {
        return Utils.plural(HelperUtils.intArg(0, n), forms.split(","));
    }

    public CharSequence lc(Object s) {
        return s != null ? s.toString().toLowerCase() : null;
    }

    public CharSequence uc(Object s) {
        return s != null ? s.toString().toUpperCase() : null;
    }

    public CharSequence pn(Object s) {
        return s != null ? Utils.toPathName(s.toString()) : null;
    }

}