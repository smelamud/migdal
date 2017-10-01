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
        CharSequence name = options.hash("name", "");
        CharSequence uuidName = options.hash("uuidName", "");
        CharSequence uuid = options.hash("uuid", "");

        return uploader(loaded, smallUrl, largeUrl, name, uuidName, uuid);
    }

    private CharSequence uploader(CharSequence loaded, CharSequence smallUrl, CharSequence largeUrl,
                                  CharSequence name, CharSequence uuidName, CharSequence uuid) {
        StringBuilder buf = new StringBuilder();
        buf.append("<div class=\"image-uploader\">");
        buf.append(formsHelperSource.hidden(uuidName.toString(), uuid, "image-uploader-uuid"));
        if (StringUtils.hasText(loaded)) {
            buf.append("<span class=\"image-uploader-loaded\">");
            buf.append("<input type=\"button\" class=\"image-uploader-delete btn btn-default btn-xs\"");
            buf.append(" value=\"Удалить\">&nbsp;<i>");
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
            buf.append("</span>");
        }
        buf.append("<input class=\"image-uploader-file\"");
        if (StringUtils.hasText(loaded)) {
            buf.append(" style=\"display: none\"");
        }
        buf.append(" type=\"file\" name=\"");
        buf.append(name);
        buf.append("\" size=\"35\"></div>");
        return new Handlebars.SafeString(buf);
    }

    public CharSequence formUploader(Options options) {
        CharSequence title = HelperUtils.mandatoryHash("title", options);
        CharSequence comment = options.hash("comment", "");
        boolean mandatory = HelperUtils.boolArg(options.hash("mandatory", false));
        CharSequence loaded = options.hash("loaded", "");
        CharSequence smallUrl = options.hash("smallUrl", "");
        CharSequence largeUrl = options.hash("largeUrl", "");
        CharSequence name = options.hash("name", "");
        CharSequence uuidName = options.hash("uuidName", "");
        CharSequence uuid = options.hash("uuid", "");

        StringBuilder buf = new StringBuilder();
        buf.append(formsHelperSource.formLineBegin(title, name.toString(), mandatory, comment, null, options));
        buf.append(uploader(loaded, smallUrl, largeUrl, name, uuidName, uuid));
        buf.append(formsHelperSource.formLineEnd());
        return new Handlebars.SafeString(buf);
    }

}