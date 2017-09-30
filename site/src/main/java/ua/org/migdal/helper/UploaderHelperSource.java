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
        CharSequence smallTitle = options.hash("smallTitle", "");
        CharSequence largeTitle = options.hash("largeTitle", "");
        CharSequence smallName = options.hash("smallName", "");
        CharSequence name = options.hash("name", "");
        CharSequence delName = options.hash("delName", "");

        return uploader(loaded, smallUrl, largeUrl, smallTitle, largeTitle, smallName, name, delName);
    }

    private CharSequence uploader(CharSequence loaded, CharSequence smallUrl, CharSequence largeUrl,
                                  CharSequence smallTitle, CharSequence largeTitle,
                                  CharSequence smallName, CharSequence name, CharSequence delName) {
        StringBuilder buf = new StringBuilder();
        if (!StringUtils.isEmpty(loaded)) {
            buf.append("<p><i>");
            buf.append(loaded);
            buf.append("</i>");
            if (!StringUtils.isEmpty(largeUrl)) {
                buf.append("&nbsp;&nbsp;<b>(</b>&nbsp;");
                if (!StringUtils.isEmpty(smallUrl)) {
                    buf.append("<a href=\"");
                    buf.append(HelperUtils.he(smallUrl));
                    buf.append("\">маленькая</a>&nbsp;");
                }
                buf.append("<a href=\"");
                buf.append(HelperUtils.he(largeUrl));
                buf.append("\">большая</a>&nbsp;<b>)</b>");
            }
            buf.append("<br>");
            formsHelperSource.checkbox("Удалить", delName, 1, false, null, null);
            buf.append("</p>");
        }
        if (!StringUtils.isEmpty(smallName)) {
            if (!StringUtils.isEmpty(largeTitle)) {
                buf.append(largeTitle);
                buf.append("&nbsp;");
            }
            buf.append("<input type=\"file\" name=\"");
            buf.append(name);
            buf.append("\" size=\"35\"><br>");
            if (!StringUtils.isEmpty(smallTitle)) {
                buf.append(smallTitle);
                buf.append("&nbsp;");
            }
            buf.append("<input type=\"file\" name=\"");
            buf.append(smallName);
            buf.append("\" size=\"35\">");
        } else {
            if (!StringUtils.isEmpty(largeTitle)) {
                buf.append(largeTitle);
                buf.append("&nbsp;");
            }
            buf.append("<input type=\"file\" name=\"");
            buf.append(name);
            buf.append("\" size=\"35\">");
        }
        return new Handlebars.SafeString(buf);
    }

    public CharSequence formUploader(Options options) {
        CharSequence title = HelperUtils.mandatoryHash("title", options);
        CharSequence comment = options.hash("comment", "");
        boolean mandatory = HelperUtils.boolArg(options.hash("mandatory", false));
        String loaded = options.hash("loaded", "");
        String smallUrl = options.hash("smallUrl", "");
        String largeUrl = options.hash("largeUrl", "");
        String smallTitle = options.hash("smallTitle", "");
        String largeTitle = options.hash("largeTitle", "");
        String smallName = options.hash("smallName", "");
        String name = options.hash("name", "");
        String delName = options.hash("delName", "");

        StringBuilder buf = new StringBuilder();
        buf.append(formsHelperSource.formLineBegin(title, name, mandatory, comment, null, options));
        buf.append(uploader(loaded, smallUrl, largeUrl, smallTitle, largeTitle, smallName, name, delName));
        buf.append(formsHelperSource.formLineEnd());
        return new Handlebars.SafeString(buf);
    }

}