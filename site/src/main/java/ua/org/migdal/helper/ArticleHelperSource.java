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

    public CharSequence article(Posting posting, Options options) {
        List<InnerImage> innerImages = options.hash("innerImages");

        MtextConverted converted = mtextConverter.convert(posting.getLargeBodyMtext(), innerImages);

        StringBuilder buf = new StringBuilder();
//<article_editmode editing!='$posting.isWritable'>
        buf.append(converted.getHtmlBody());
//                </article_editmode>
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
/*<if what = '$$editmode' >
  <posting_obj id = '$data.Id' >
  <assign name = 'edithref'
            value#='${,GrpDetailsHref}images/insert/?par=$data.Par&editid=$image.Id' >
  <assign name = 'rmhref'
            value#=
            '/actions/posting/image/modify
            ?edittag=1&insert=1&postid=$data.Id&editid=0&par=$data.Par&okdir=$#-&faildir=$#-'>
 <else>*/
        String editHref = "";
        String rmHref = "";
//</if>
        CharSequence titleHtml = mtextConverter.toHtml(image.getTitleMtext());
        CharSequence titleLineHtml = mtextConverter.toHtml(image.getTitleLineMtext());
        buf.append(postingHelperSource.entryImage(image, align, false, false, null, String.format("article-%d", id),
                titleHtml, 0, titleLineHtml, 0, 0, false, null, editHref, rmHref, false, false));
//        } else {
/*<if what = '$$editmode' >
  <ifne what = '$data.Align' >
   <assign name = 'align' value#='style="float: $data.Align; clear: $data.Align"' >
  <else>
   <assign name = 'align' value = '' >
  </ifne >
  <posting_obj id = '$data.Id' >
  <notenglish >
   <assign name = 'ititle' value = 'Вставить картинку' >
  <else>
   <assign name = 'ititle' value = 'Insert a Picture' >
  </notenglish >
  <div class='insert-image' $align ><span >
   <a href = '${,GrpDetailsHref}images/insert/?par=$data.Par'
            class='eightpt' > $ititle </a >
  </span ></div >
 </if>*/
        }
        return new SafeString(buf);
    }

}
