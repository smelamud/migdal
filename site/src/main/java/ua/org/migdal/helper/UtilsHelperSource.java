package ua.org.migdal.helper;

import java.io.IOException;
import java.io.UnsupportedEncodingException;
import java.net.URLEncoder;

import com.github.jknack.handlebars.Options;

@HelperSource
public class UtilsHelperSource {

    public CharSequence assign(String variableName, Options options) {
        try {
            CharSequence finalValue = options.apply(options.fn);
            options.data(variableName, finalValue.toString().trim());
        } catch (IOException e) {
        }
        return "";
    }

    public CharSequence ue(String s) {
        try {
            return URLEncoder.encode(s, "UTF-8");
        } catch (UnsupportedEncodingException e) {
            return "ue:" + e.getMessage();
        }
    }

}
