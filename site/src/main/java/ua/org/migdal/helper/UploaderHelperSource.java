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
        CharSequence delName = options.hash("delName", "");

        return uploader(loaded, smallUrl, largeUrl, largeTitle, name, delName);
    }

    private CharSequence uploader(CharSequence loaded, CharSequence smallUrl, CharSequence largeUrl,
                                  CharSequence largeTitle, CharSequence name, CharSequence delName) {
        StringBuilder buf = new StringBuilder();
        if (StringUtils.hasText(loaded)) {
            buf.append("<p><i>");
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
            buf.append("<br>");
            buf.append(formsHelperSource.checkbox("Удалить", delName, 1, false, null, null));
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
        CharSequence delName = options.hash("delName", "");

        StringBuilder buf = new StringBuilder();
        buf.append(formsHelperSource.formLineBegin(title, name.toString(), mandatory, comment, null, options));
        buf.append(uploader(loaded, smallUrl, largeUrl, largeTitle, name, delName));
        buf.append(formsHelperSource.formLineEnd());
        return new Handlebars.SafeString(buf);
    }

}