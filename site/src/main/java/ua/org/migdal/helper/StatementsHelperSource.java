package ua.org.migdal.helper;

import java.io.IOException;

import com.github.jknack.handlebars.Handlebars.SafeString;
import com.github.jknack.handlebars.Options;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

@HelperSource
public class StatementsHelperSource {

    private static Logger log = LoggerFactory.getLogger(StatementsHelperSource.class);

    public CharSequence assign(String variableName, Options options) throws IOException {
        CharSequence finalValue = options.apply(options.fn);
        finalValue = finalValue instanceof SafeString ? finalValue : finalValue.toString().trim();
        options.data(variableName, finalValue);
        return "";
    }

    public CharSequence not(Object value) {
        return Boolean.toString(!HelperUtils.boolArg(value));
    }

    public CharSequence ifeq(String value1, String value2, Options options) throws IOException {
        boolean condition = value1 == null && value2 == null || value1 != null && value1.equals(value2);
        return condition ? options.apply(options.fn) : options.apply(options.inverse);
    }

    public CharSequence ifne(String value1, String value2, Options options) throws IOException {
        boolean condition = value1 == null && value2 != null || value1 != null && !value1.equals(value2);
        return condition ? options.apply(options.fn) : options.apply(options.inverse);
    }

    public CharSequence ifieq(Object value1, Object value2, Options options) throws IOException {
        boolean condition = HelperUtils.intArg(0, value1) == HelperUtils.intArg(1, value2);
        return condition ? options.apply(options.fn) : options.apply(options.inverse);
    }

    public CharSequence ifine(Object value1, Object value2, Options options) throws IOException {
        boolean condition = HelperUtils.intArg(0, value1) != HelperUtils.intArg(1, value2);
        return condition ? options.apply(options.fn) : options.apply(options.inverse);
    }

    public CharSequence ifgt(Object value1, Object value2, Options options) throws IOException {
        boolean condition = HelperUtils.intArg(0, value1) > HelperUtils.intArg(1, value2);
        return condition ? options.apply(options.fn) : options.apply(options.inverse);
    }

    public CharSequence iflt(Object value1, Object value2, Options options) throws IOException {
        boolean condition = HelperUtils.intArg(0, value1) < HelperUtils.intArg(1, value2);
        return condition ? options.apply(options.fn) : options.apply(options.inverse);
    }

    public CharSequence ifge(Object value1, Object value2, Options options) throws IOException {
        boolean condition = HelperUtils.intArg(0, value1) >= HelperUtils.intArg(1, value2);
        return condition ? options.apply(options.fn) : options.apply(options.inverse);
    }

    public CharSequence ifle(Object value1, Object value2, Options options) throws IOException {
        boolean condition = HelperUtils.intArg(0, value1) <= HelperUtils.intArg(1, value2);
        return condition ? options.apply(options.fn) : options.apply(options.inverse);
    }

}