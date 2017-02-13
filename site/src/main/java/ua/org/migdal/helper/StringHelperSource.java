package ua.org.migdal.helper;

import java.io.UnsupportedEncodingException;
import java.net.URLEncoder;

import org.springframework.web.util.HtmlUtils;

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

    public CharSequence plural(String nS, String forms) {
        long n = HelperUtils.intArg(0, nS);
        String[] formsA = forms.split(",");
        long a = n % 10;
        long b = n / 10 % 10;
        return b == 1 || a >= 5 || a == 0
                ? formsA[2]
                : (a == 1 ? formsA[0] : formsA[1]);
    }

}
