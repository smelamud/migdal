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
            buf.append("<a");
            HelperUtils.appendAttr(buf, "href",
                    UriComponentsBuilder.fromUriString(requestContext.getLocation())
                            .replaceQueryParam("offset", prevOffset)
                            .replaceQueryParam("tid")
                            .toUriString());
            HelperUtils.appendAttr(buf, "title", !requestContext.isEnglish() ? "Предыдущая страница" : "Previous page");
            buf.append('>');
            HelperUtils.safeAppend(buf, imagesHelperSource.image("/pics/left.gif"));
            buf.append("</a>");
        } else {
            buf.append("&nbsp;");
        }
        buf.append("</span>");
        buf.append("<form class=\"goto-page\"");
        HelperUtils.appendAttr(buf, "data-page-size", page.getSize());
        HelperUtils.appendAttr(buf, "data-total-pages", page.getTotalPages());
        buf.append('>');
        buf.append(!requestContext.isEnglish() ? "стр. " : "page ");
        HelperUtils.safeAppend(buf, formsHelperSource.edit("value", Integer.toString(page.getNumber() + 1),
                                                           "3", "5", null, null));
        buf.append(!requestContext.isEnglish() ? " из " : " of ");
        buf.append(page.getTotalPages());
        buf.append("</form>");
        buf.append("<span class=\"button\">");
        if (page.hasNext()) {
            long nextOffset = page.nextPageable().getOffset();
            buf.append("<a");
            HelperUtils.appendAttr(buf, "href",
                    UriComponentsBuilder.fromUriString(requestContext.getLocation())
                            .replaceQueryParam("offset", nextOffset)
                            .replaceQueryParam("tid")
                            .toUriString());
            HelperUtils.appendAttr(buf, "title", !requestContext.isEnglish() ? "Следующая страница" : "Next page");
            buf.append('>');
            HelperUtils.safeAppend(buf, imagesHelperSource.image("/pics/right.gif"));
            buf.append("</a>");
        } else {
            buf.append("&nbsp;");
        }
        buf.append("</span>");
        buf.append("</div>");
        return new SafeString(buf);
    }

    public CharSequence naviSort(Options options) {
        CharSequence sort = HelperUtils.mandatoryHash("sort", options);
        CharSequence value = HelperUtils.mandatoryHash("value", options);
        CharSequence title = HelperUtils.mandatoryHash("title", options);

        StringBuilder buf = new StringBuilder();
        if (!sort.toString().equals(value.toString())) {
            buf.append("<a");
            HelperUtils.appendAttr(buf, "href", "?sort=" + HelperUtils.ue(value));
            buf.append('>');
            HelperUtils.safeAppend(buf, title);
            buf.append("</a>");
        } else {
            buf.append("<b>");
            HelperUtils.safeAppend(buf, title);
            buf.append("</b>");
        }
        return new SafeString(buf);
    }

}