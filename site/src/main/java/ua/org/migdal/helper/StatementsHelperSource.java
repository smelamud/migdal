package ua.org.migdal.helper;

import java.io.IOException;

import com.github.jknack.handlebars.Options;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

@HelperSource
public class StatementsHelperSource {

    private static Logger log = LoggerFactory.getLogger(StatementsHelperSource.class);

    public CharSequence assign(String variableName, Options options) throws IOException {
        CharSequence finalValue = options.apply(options.fn);
        options.data(variableName, finalValue.toString().trim());
        return "";
    }

    public CharSequence ifeq(String value1, String value2, Options options) throws IOException {
        boolean condition = value1 == null && value2 == null || value1 != null && value1.equals(value2);
        return condition ? options.apply(options.fn) : options.apply(options.inverse);
    }

    public CharSequence ifne(String value1, String value2, Options options) throws IOException {
        boolean condition = value1 == null && value2 != null || value1 != null && !value1.equals(value2);
        return condition ? options.apply(options.fn) : options.apply(options.inverse);
    }

}
