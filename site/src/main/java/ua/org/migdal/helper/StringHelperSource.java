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

}
