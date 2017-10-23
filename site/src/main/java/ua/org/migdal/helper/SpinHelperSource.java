package ua.org.migdal.helper;

import javax.inject.Inject;
import org.springframework.data.domain.Page;
import org.springframework.web.util.UriComponentsBuilder;

import com.github.jknack.handlebars.Handlebars.SafeString;
import com.github.jknack.handlebars.Options;

import ua.org.migdal.helper.util.HelperUtils;
import ua.org.migdal.session.RequestContext;

@HelperSource
public class SpinHelperSource {

    @Inject
    private RequestContext requestContext;

    @Inject
    private ImagesHelperSource imagesHelperSource;

    @Inject
    private FormsHelperSource formsHelperSource;

    public CharSequence spin(Page<?> page, Options options) {
        if (!page.hasNext() && !page.hasPrevious()) {
            return "";
        }

        StringBuilder buf = new StringBuilder();
        buf.append("<div class=\"spin\">");
        buf.append("<span class=\"button\">");
        if (page.hasPrevious()) {
            long prevOffset = page.previousPageable().getOffset();
            buf.append("<a href=\"");
            HelperUtils.safeAppend(buf,
                    UriComponentsBuilder.fromUriString(requestContext.getLocation())
                            .replaceQueryParam("offset", prevOffset)
                            .replaceQueryParam("tid")
                            .toUriString());
            buf.append("\" title=\"Предыдущая страница\">");
            HelperUtils.safeAppend(buf, imagesHelperSource.image("/pics/left.gif"));
            buf.append("</a>");
        } else {
            buf.append("&nbsp;");
        }
        buf.append("</span>");
        buf.append("<form class=\"goto-page\"");
        buf.append(" data-page-size=\"");
        buf.append(page.getSize());
        buf.append('"');
        buf.append(" data-total-pages=\"");
        buf.append(page.getTotalPages());
        buf.append("\">");
        buf.append("стр. ");
        HelperUtils.safeAppend(buf, formsHelperSource.edit("value", Integer.toString(page.getNumber() + 1),
                                                           "3", "5", null));
        buf.append(" из ");
        buf.append(page.getTotalPages());
        buf.append("</form>");
        buf.append("<span class=\"button\">");
        if (page.hasNext()) {
            long nextOffset = page.nextPageable().getOffset();
            buf.append("<a href=\"");
            HelperUtils.safeAppend(buf,
                    UriComponentsBuilder.fromUriString(requestContext.getLocation())
                            .replaceQueryParam("offset", nextOffset)
                            .replaceQueryParam("tid")
                            .toUriString());
            buf.append("\" title=\"Следующая страница\">");
            HelperUtils.safeAppend(buf, imagesHelperSource.image("/pics/right.gif"));
            buf.append("</a>");
        } else {
            buf.append("&nbsp;");
        }
        buf.append("</span>");
        buf.append("</div>");
        return new SafeString(buf);
    }

}