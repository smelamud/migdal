package ua.org.migdal.helper;

import java.io.UnsupportedEncodingException;
import java.net.URLEncoder;

@HelperSource
public class UtilsHelperSource {

    public CharSequence ue(String s) {
        try {
            return URLEncoder.encode(s, "UTF-8");
        } catch (UnsupportedEncodingException e) {
            return "ue:" + e.getMessage();
        }
    }

}
