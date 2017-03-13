package ua.org.migdal.helper;

import java.io.IOException;

import com.github.jknack.handlebars.Handlebars.SafeString;
import com.github.jknack.handlebars.Options;

@HelperSource
public class CatalogHelperSource {

    public CharSequence catalogTable(Options options) throws IOException {
        boolean compact = HelperUtils.boolArg(options.hash("compact", false));

        StringBuilder buf = new StringBuilder();
        buf.append("<table");
        if (!compact) {
            buf.append(" width=\"100%\"");
        }
        buf.append("class=\"catalog\"><tr><td>");
        buf.append("<table width=\"100%\" class=\"catalog-layer\" cellspacing=\"1\" cellpadding=\"3\">");
        buf.append(options.apply(options.fn));
        buf.append("</table>");
        buf.append("</td></tr></table>");
        return new SafeString(buf);
    }

}