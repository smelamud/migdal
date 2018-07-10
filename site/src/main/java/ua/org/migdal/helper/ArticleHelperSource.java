package ua.org.migdal.helper;

import javax.inject.Inject;

import com.github.jknack.handlebars.Handlebars.SafeString;
import com.github.jknack.handlebars.Options;

import ua.org.migdal.data.Posting;
import ua.org.migdal.helper.util.HelperUtils;
import ua.org.migdal.session.RequestContext;

@HelperSource
public class ArticleHelperSource {

    @Inject
    private RequestContext requestContext;

    @Inject
    private PostingHelperSource postingHelperSource;

    public CharSequence imagesLink(Posting posting) {
        StringBuilder buf = new StringBuilder();
        buf.append("<a");
        HelperUtils.appendAttr(buf, "href", String.format("%simages/", posting.getGrpDetailsHref()));
        buf.append('>');
        if (!requestContext.isEnglish()) {
            buf.append("Список иллюстраций");
        } else {
            buf.append("List of pictures");
        }
        buf.append("</a>");
        return new SafeString(buf);
    }

    public CharSequence articleControls(Options options) {
        if (requestContext.isPrintMode()) {
            return "";
        }

        Posting posting = HelperUtils.mandatoryHash("posting", options);

        StringBuilder buf = new StringBuilder();
        buf.append("<div class=\"controls-line\"><div class=\"controls\">");
        buf.append(postingHelperSource.printLink(posting));
        if (posting.isWritable()) {
            buf.append(" &nbsp;&nbsp; ");
            buf.append(postingHelperSource.editLink(posting));
            buf.append(" &nbsp;&nbsp; ");
            buf.append(imagesLink(posting));
        }
        buf.append("</div></div>");
        return new SafeString(buf);
    }

}
