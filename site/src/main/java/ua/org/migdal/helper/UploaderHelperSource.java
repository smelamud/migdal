package ua.org.migdal.helper;

import javax.inject.Inject;

import com.github.jknack.handlebars.Handlebars;
import com.github.jknack.handlebars.Options;

import org.springframework.util.StringUtils;
import ua.org.migdal.helper.util.HelperUtils;

@HelperSource
public class UploaderHelperSource {

    @Inject
    private FormsHelperSource formsHelperSource;

    public CharSequence uploader(Options options) {
        CharSequence loaded = options.hash("loaded", "");
        CharSequence smallUrl = options.hash("smallUrl", "");
        CharSequence largeUrl = options.hash("largeUrl", "");
        CharSequence largeTitle = options.hash("largeTitle", "");
        CharSequence name = options.hash("name", "");

        return uploader(loaded, smallUrl, largeUrl, largeTitle, name);
    }

    private CharSequence uploader(CharSequence loaded, CharSequence smallUrl, CharSequence largeUrl,
                                  CharSequence largeTitle, CharSequence name) {
        StringBuilder buf = new StringBuilder();
        if (StringUtils.hasText(loaded)) {
            buf.append("<p><input type=\"button\" class=\"btn btn-default btn-xs\" value=\"Удалить\">&nbsp;<i>");
            buf.append(loaded);
            buf.append("</i>");
            if (!StringUtils.isEmpty(largeUrl)) {
                buf.append("&nbsp;&nbsp;<b>(</b>&nbsp;");
                if (!StringUtils.isEmpty(smallUrl)) {
                    buf.append("<a href=\"");
                    buf.append(HelperUtils.he(smallUrl));
                    buf.append("\">маленькая</a>,&nbsp;");
                }
                buf.append("<a href=\"");
                buf.append(HelperUtils.he(largeUrl));
                buf.append("\">большая</a>&nbsp;<b>)</b>");
            }
            buf.append("</p>");
        }
        if (!StringUtils.isEmpty(largeTitle)) {
            buf.append(largeTitle);
            buf.append("&nbsp;");
        }
        buf.append("<input type=\"file\" name=\"");
        buf.append(name);
        buf.append("\" size=\"35\">");
        return new Handlebars.SafeString(buf);
    }

    public CharSequence formUploader(Options options) {
        CharSequence title = HelperUtils.mandatoryHash("title", options);
        CharSequence comment = options.hash("comment", "");
        boolean mandatory = HelperUtils.boolArg(options.hash("mandatory", false));
        CharSequence loaded = options.hash("loaded", "");
        CharSequence smallUrl = options.hash("smallUrl", "");
        CharSequence largeUrl = options.hash("largeUrl", "");
        CharSequence largeTitle = options.hash("largeTitle", "");
        CharSequence name = options.hash("name", "");

        StringBuilder buf = new StringBuilder();
        buf.append(formsHelperSource.formLineBegin(title, name.toString(), mandatory, comment, null, options));
        buf.append(uploader(loaded, smallUrl, largeUrl, largeTitle, name));
        buf.append(formsHelperSource.formLineEnd());
        return new Handlebars.SafeString(buf);
    }

}