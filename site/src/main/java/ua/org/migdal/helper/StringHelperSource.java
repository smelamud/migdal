package ua.org.migdal.helper;

import java.io.UnsupportedEncodingException;
import java.net.URLEncoder;

@HelperSource
public class StringHelperSource {

    public CharSequence ue(String s) {
        try {
            return URLEncoder.encode(s, "UTF-8");
        } catch (UnsupportedEncodingException e) {
            return "ue:" + e.getMessage();
        }
    }

    public CharSequence asp(String s) {
        return s != null ? s.replaceAll(">\\s+", ">") : null;
    }

    public CharSequence plural(String nS, String forms) {
        int n = HelperUtils.intArgument(0, nS);
        String[] formsA = forms.split(",");
        int a = n % 10;
        int b = n / 10 % 10;
        return b == 1 || a >= 5 || a == 0
                ? formsA[2]
                : (a == 1 ? formsA[0] : formsA[1]);
    }

}
