package ua.org.migdal.helper;

import java.io.UnsupportedEncodingException;
import java.net.URLEncoder;

import org.springframework.web.util.HtmlUtils;
import ua.org.migdal.util.Utils;

@HelperSource
public class StringHelperSource {

    public CharSequence ue(String s) {
        try {
            return URLEncoder.encode(s, "UTF-8");
        } catch (UnsupportedEncodingException e) {
            return "ue:" + e.getMessage();
        }
    }

    public CharSequence he(String s) {
        return HtmlUtils.htmlEscape(s);
    }

    public CharSequence asp(String s) {
        return s != null ? s.replaceAll(">\\s+", ">") : null;
    }

    public CharSequence plural(Object n, String forms) {
        return Utils.plural(HelperUtils.intArg(0, n), forms.split(","));
    }

}
