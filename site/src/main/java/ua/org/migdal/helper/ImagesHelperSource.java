package ua.org.migdal.helper;

import com.github.jknack.handlebars.Handlebars;
import com.github.jknack.handlebars.Options;
import org.springframework.web.util.HtmlUtils;

@HelperSource
public class ImagesHelperSource {

    public CharSequence image(String href, Options options) {
        StringBuilder buf = new StringBuilder();
        buf.append("<img src=\"");
        buf.append(href);
        buf.append('"');
        if (!options.<Boolean>get("englishDomain") || options.hash("alt_en") == null) {
            appendHashParam(buf, "alt", options);
        } else {
            appendHashParam(buf, "alt_en", "alt", options);
        }
        appendHashParam(buf, "title", options);
        appendHashParam(buf, "class", options);
        appendHashParam(buf, "id", options);
        appendHashParam(buf, "data_id", "data-id", options);
        appendHashParam(buf, "data_value", "data-value", options);
        appendHashParam(buf, "style", options);
        buf.append('>');
        return new Handlebars.SafeString(buf);
    }

    private void appendHashParam(StringBuilder buf, String name, Options options) {
        appendHashParam(buf, name, name, options);
    }

    private void appendHashParam(StringBuilder buf, String name, String attrName, Options options) {
        if (options.hash(name) != null) {
            buf.append(' ');
            buf.append(attrName);
            buf.append("=\"");
            buf.append(HtmlUtils.htmlEscape(options.hash(name)));
            buf.append('"');
        }
    }

}
