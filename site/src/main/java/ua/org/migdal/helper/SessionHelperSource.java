package ua.org.migdal.helper;

import java.io.IOException;

import com.github.jknack.handlebars.Options;

@HelperSource
public class SessionHelperSource {

    public CharSequence english(Options options) throws IOException {
        return options.get("userDomain").equals("english")
                ? options.apply(options.fn)
                : options.apply(options.inverse);
    }

    public CharSequence notEnglish(Options options) throws IOException {
        return !options.get("userDomain").equals("english")
                ? options.apply(options.fn)
                : options.apply(options.inverse);
    }

}