package ua.org.migdal.helper;

import com.github.jknack.handlebars.Handlebars;
import com.github.jknack.handlebars.Options;
import org.springframework.web.util.HtmlUtils;
import ua.org.migdal.helper.exception.MissingArgumentException;

@HelperSource
public class FormsHelperSource {

    public CharSequence hidden(Options options) {
        String name = options.hash("name");
        if (name == null) {
            throw new MissingArgumentException("name");
        }
        String value = options.hash("value", "");
        String html = String.format("<input type=\"hidden\" name=\"%s\" value=\"%s\">", name, value);
        return new Handlebars.SafeString(html);
    }

    public CharSequence checkboxButton(Options options) {
        StringBuilder buf = new StringBuilder();
        buf.append("<input type=\"checkbox\"");
        appendHashParam(buf, "name", "name", null, options);
        appendHashParam(buf, "value", "value", "1", options);
        boolean checked = options.<Boolean> hash("checked", false);
        if (checked) {
            buf.append(" checked");
        }
        appendHashParam(buf,"id", options);
        appendHashParam(buf, "class", options);
        return new Handlebars.SafeString(buf);
    }

    public CharSequence checkbox(Options options) {
        StringBuilder buf = new StringBuilder();
        buf.append("<label>");
        buf.append(checkboxButton(options));
        String title = options.hash("title", "");
        buf.append(' ');
        buf.append(title);
        buf.append("</label>");
        return new Handlebars.SafeString(buf);
    }

    public CharSequence radioButton(Options options) {
        StringBuilder buf = new StringBuilder();
        buf.append("<input type=\"radio\"");
        appendHashParam(buf, "name", "name", null, options);
        appendHashParam(buf, "value", "value", null, options);
        boolean checked = options.<Boolean> hash("checked", false);
        if (checked) {
            buf.append(" checked");
        }
        appendHashParam(buf,"id", options);
        appendHashParam(buf, "class", options);
        appendHashParam(buf, "onclick", options);
        return new Handlebars.SafeString(buf);
    }

    public CharSequence radio(Options options) {
        StringBuilder buf = new StringBuilder();
        buf.append("<label>");
        buf.append(radioButton(options));
        String title = options.hash("title", "");
        buf.append(' ');
        buf.append(title);
        buf.append("</label>");
        return new Handlebars.SafeString(buf);
    }

    public CharSequence selectOption(Options options) {
        boolean selected = options.<Boolean> hash("selected", false);
        String value = options.hash("value");
        if (value == null) {
            throw new MissingArgumentException("value");
        }
        String title = options.hash("title", "");
        if (selected) {
            return new Handlebars.SafeString(String.format("<option value=\"%s\" selected>%s", value, title));
        } else {
            return new Handlebars.SafeString(String.format("<option value=\"%s\">%s", value, title));
        }
    }

    private void appendHashParam(StringBuilder buf, String name, Options options) {
        appendHashParam(buf, name, name, options);
    }

    private void appendHashParam(StringBuilder buf, String name, String attrName, Options options) {
        appendAttribute(buf, attrName, options.hash(name));
    }

    private void appendHashParam(StringBuilder buf, String name, String attrName, String defaultValue,
                                 Options options) {
        String value = defaultValue != null ? options.hash(name, defaultValue) : options.hash(name);
        if (value == null) {
            throw new MissingArgumentException(name);
        }
        appendAttribute(buf, attrName, value);
    }

    private void appendAttribute(StringBuilder buf, String attributeName, Object value) {
        if (value != null) {
            buf.append(' ');
            buf.append(attributeName);
            buf.append("=\"");
            buf.append(HtmlUtils.htmlEscape(value.toString()));
            buf.append('"');
        }
    }

}