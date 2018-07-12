package ua.org.migdal.helper;

import java.util.List;
import javax.inject.Inject;

import com.github.jknack.handlebars.Handlebars.SafeString;
import com.github.jknack.handlebars.Options;

import org.springframework.util.StringUtils;
import ua.org.migdal.data.Image;
import ua.org.migdal.data.InnerImage;
import ua.org.migdal.data.Posting;
import ua.org.migdal.helper.util.HelperUtils;
import ua.org.migdal.mtext.MtextConverted;
import ua.org.migdal.mtext.MtextConverter;
import ua.org.migdal.session.RequestContext;

@HelperSource
public class ArticleHelperSource {

    @Inject
    private RequestContext requestContext;

    @Inject
    private MtextConverter mtextConverter;

    @Inject
    private PostingHelperSource postingHelperSource;

    public CharSequence imagesLink(Posting posting) {
        StringBuilder buf = new StringBuilder();
        buf.append("<a id=\"switch-image-editing-on\" href=\"#image-editing\">");
        if (!requestContext.isEnglish()) {
            buf.append("Расставить картинки");
        } else {
            buf.append("Arrange pictures");
        }
        buf.append("</a>");
        buf.append("<a id=\"switch-image-editing-off\" href=\"#\">");
        if (!requestContext.isEnglish()) {
            buf.append("Закончить расстановку картинок");
        } else {
            buf.append("End the picture arrangement");
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

    public CharSequence article(Posting posting, Options options) {
        List<InnerImage> innerImages = options.hash("innerImages");

        MtextConverted converted = mtextConverter.convert(posting.getLargeBodyMtext(), innerImages);

        StringBuilder buf = new StringBuilder();
        buf.append(converted.getHtmlBody());
        if (!StringUtils.isEmpty(posting.getSource())) {
            buf.append("<p class=\"source\">");
            if (!requestContext.isEnglish()) {
                buf.append("Источник: ");
            } else {
                buf.append("Source: ");
            }
            buf.append(mtextConverter.toHtml(posting.getSourceMtext()));
            buf.append("</p>");
        }
        buf.append("<div class=\"clear-floats\"></div>");
        if (!StringUtils.isEmpty(converted.getHtmlFootnotes().toString())) {
            buf.append("<hr class=\"notes\" align=\"left\">");
            buf.append(converted.getHtmlFootnotes());
        }
        return new SafeString(buf);
    }

    public CharSequence articleImage(long id, int paragraph, Image image, String align) {
        StringBuilder buf = new StringBuilder();
        if (image != null) {
            CharSequence titleHtml = mtextConverter.toHtml(image.getTitleMtext());
            CharSequence titleLineHtml = mtextConverter.toHtml(image.getTitleLineMtext());
            String editHref = String.format("/insert-image/?postingId=%d&paragraph=%d&imageId=%d",
                    id, paragraph, image.getId());
            String rmHref = String.format("/actions/posting/image/modify?insert=1&postingId=%s&imageId=0&paragraph=%s",
                    id, paragraph);
            buf.append(postingHelperSource.entryImage(image, align, false, false, null, String.format("article-%d", id),
                    titleHtml, 0, titleLineHtml, 0, 0, false, null, editHref, rmHref, true, false));
        } else {
            buf.append("<div class=\"insert-image\"");
            if (!StringUtils.isEmpty(align)) {
                HelperUtils.appendAttr(buf, "align", String.format("float %s; clear %s", align, align));
            }
            buf.append("><span>");
            buf.append(String.format("<a href=\"/insert-image/?paragraph=%d\" class=\"eightpt\">", paragraph));
            if (!requestContext.isEnglish()) {
                buf.append("Вставить картинку");
            } else {
                buf.append("Insert a Picture");
            }
            buf.append("</a></span></div>");
        }
        return new SafeString(buf);
    }

}
