package ua.org.migdal.helper;

import java.time.LocalDateTime;

import javax.inject.Inject;

import com.github.jknack.handlebars.Handlebars.SafeString;
import com.github.jknack.handlebars.Options;
import org.springframework.util.StringUtils;
import ua.org.migdal.data.Entry;
import ua.org.migdal.data.Posting;
import ua.org.migdal.helper.calendar.CalendarType;
import ua.org.migdal.helper.calendar.Formatter;
import ua.org.migdal.helper.util.HelperUtils;
import ua.org.migdal.session.RequestContext;

@HelperSource
public class PostingHelperSource {

    @Inject
    private RequestContext requestContext;

    @Inject
    private ImagesHelperSource imagesHelperSource;

    public CharSequence sentView(Options options) {
        LocalDateTime timestamp = HelperUtils.timestampArg("date", options.hash("date"));
        return new SafeString(Formatter.format(CalendarType.GREGORIAN_EN, "dd.MM.yyyy'&nbsp;'HH:mm", timestamp));
    }

    public CharSequence senderLink(Entry entry, Options options) {
        if (requestContext.isEnglish()) {
            return "";
        }

        StringBuilder buf = new StringBuilder();
        if (!entry.isUserGuest()) {
            if (entry.isUserVisible()) {
                buf.append("<a href=\"/users/");
                buf.append(HelperUtils.ue(entry.getUserFolder()));
                buf.append("/\" data-id=\"");
                buf.append(entry.getUser().getId());
                buf.append("\" class=\"name\">");
                buf.append(HelperUtils.he(entry.getUserLogin()));
                buf.append("</a>");
            } else {
                buf.append("<span class=\"name\">");
                buf.append(HelperUtils.he(entry.getUserLogin()));
                buf.append("</span>");
            }
        } else {
            buf.append("<span class=\"guest-name\">");
            buf.append(HelperUtils.he(entry.getGuestLogin()));
            buf.append("</span>");
        }
        return new SafeString(buf);
    }

    public CharSequence postingImage(Options options) {
        Posting posting = HelperUtils.mandatoryHash("posting", options);
        CharSequence align = options.hash("align");
        boolean noClear = HelperUtils.boolArg(options.hash("noClear"));
        boolean noMargin = HelperUtils.boolArg(options.hash("noMargin"));
        Object imageTitle = options.hash("imageTitle");
        CharSequence rel = options.hash("rel");
        Object title = options.hash("title");
        long titleLargeId = HelperUtils.intArg("titleLargeId", options.hash("titleLargeId"));
        String titleLarge = options.hash("titleLarge");
        long fixedWidth = HelperUtils.intArg("fixedWidth", options.hash("fixedWidth"));
        long fixedHeight = HelperUtils.intArg("fixedHeight", options.hash("fixedHeight"));
        boolean enlargeAlways = HelperUtils.boolArg(options.hash("enlargeAlways"));
        CharSequence href = options.hash("href");
        CharSequence editHref = options.hash("editHref");
        CharSequence rmHref = options.hash("rmHref");
        boolean hollow = HelperUtils.boolArg(options.hash("hollow"));

        String relation = "";
        if (!StringUtils.isEmpty(rel)) {
            relation = String.format(" rel=\"%s\"", rel);
        }

        String dataTitle = "";
        if (titleLargeId > 0) {
            dataTitle = String.format(" data-title-large-id=\"%d\"", titleLargeId);
        } else {
            if (!StringUtils.isEmpty(titleLarge)) {
                dataTitle = String.format(" data-title-large=\"%s\"", HelperUtils.he(titleLarge));
            }
        }

        long sizeX = fixedWidth != 0 ? fixedWidth : posting.getSmallImageX();
        long sizeY = fixedHeight != 0 ? fixedHeight : posting.getSmallImageY();

        StringBuilder buf = new StringBuilder();
        buf.append("<div class=\"pic\" style=\"width: ");
        buf.append(sizeX);
        buf.append("px");
        buf.append(imageDivFloat(align, noClear, noMargin));
        buf.append("\">");
        if (posting.isHasLargeImage() || enlargeAlways) {
            if (StringUtils.isEmpty(href)) {
                buf.append("<a class=\"enlargeable\" href=\"");
                buf.append(posting.getLargeImageUrl());
                buf.append('"');
                buf.append(dataTitle);
                buf.append(relation);
                buf.append('>');
            } else {
                buf.append("<a href=\"");
                HelperUtils.safeAppend(buf, href);
                buf.append("\">");
            }
            if (!hollow) {
                buf.append(imageTag(posting.getSmallImageUrl(), sizeX, sizeY, imageTitle));
            } else {
                buf.append("&nbsp;");
            }
            buf.append("</a>");
            if (StringUtils.isEmpty(href) && !hollow) {
                buf.append(glass(sizeX, sizeY));
            }
        } else {
            if (!StringUtils.isEmpty(href)) {
                buf.append("<a href=\"");
                HelperUtils.safeAppend(buf, href);
                buf.append("\">");
            }
            buf.append(imageTag(posting.getImageUrl(), posting.getImageX(), posting.getImageY(), imageTitle));
            if (!StringUtils.isEmpty(href)) {
                buf.append("</a>");
            }
        }
        if (!hollow && (!StringUtils.isEmpty(editHref) || !StringUtils.isEmpty(rmHref))) {
            buf.append("<div class=\"buttons\">");
            if (!StringUtils.isEmpty(editHref)) {
                if (editHref.equals("auto")) {
                    editHref = String.format("%sedit/?back=%s",
                            posting.getGrpDetailsHref(), requestContext.getLocation());
                }
                String tooltip = !requestContext.isEnglish() ? "Изменить" : "Edit";
                buf.append(imageButton("/pics/edit.png", editHref, tooltip));
            }
            if (!StringUtils.isEmpty(rmHref)) {
                String tooltip = !requestContext.isEnglish() ? "Убрать" : "Remove";
                buf.append(imageButton("/pics/remove.png", rmHref, tooltip));
            }
            buf.append("</div>");
        }
        if (!StringUtils.isEmpty(title)) {
            buf.append("<div class=\"pic-title\">");
            HelperUtils.safeAppend(buf, title);
            buf.append("</div>");
        }
        if (!hollow) {
            long ratingY = sizeY - 25;
            //<rating id='$posting.Id' value= $rating' style#='top: ${rating_y}px'>
        }
        buf.append("</div>");

        return new SafeString(buf);
    }

    private CharSequence imageDivFloat(CharSequence align, boolean noClear, boolean noMargin) {
        if (!StringUtils.isEmpty(align)) {
            StringBuilder buf = new StringBuilder();
            if (!align.equals("center")) {
                if (align.equals("left")) {
                    buf.append("; float: left");
                    if (!noClear) {
                        buf.append("; clear: left");
                    }
                    if (!noMargin) {
                        buf.append("; margin-right: 1em");
                    }
                } else {
                    buf.append("; float: right");
                    if (!noClear) {
                        buf.append("; clear: right");
                    }
                    if (!noMargin) {
                        buf.append("; margin-left: 1em");
                    }
                }
            } else {
                buf.append("; margin-left: auto; margin-right: auto");
                if (!noClear) {
                    buf.append("; clear: both");
                }
            }
            return buf;
        } else {
            return "; float: none";
        }
    }

    private CharSequence imageTag(String src, long sizeX, long sizeY, Object title) {
        StringBuilder buf = new StringBuilder();
        buf.append("<img width=\"");
        buf.append(sizeX);
        buf.append("\" height=\"");
        buf.append(sizeY);
        buf.append('"');
        if (!StringUtils.isEmpty(title)) {
            buf.append(" alt=\"");
            HelperUtils.safeAppend(buf, title);
            buf.append("\" title=\"");
            HelperUtils.safeAppend(buf, title);
            buf.append('"');
        }
        buf.append(" src=\"");
        buf.append(src);
        buf.append("\">");
        return buf;
    }

    private CharSequence glass(long sizeX, long sizeY) {
        StringBuilder buf = new StringBuilder();
        long glassX = sizeX - 22;
        long glassY = sizeY - 22;
        buf.append("<div class=\"glass\" style=\"top: ");
        buf.append(glassY);
        buf.append("px; left: ");
        buf.append(glassX);
        buf.append("px\"></div>");
        return buf;
    }

    private CharSequence imageButton(CharSequence src, CharSequence href, String tooltip) {
        StringBuilder buf = new StringBuilder();
        buf.append("<span>");
        buf.append("<a href=\"");
        HelperUtils.safeAppend(buf, href);
        buf.append("\">");
        buf.append(imagesHelperSource.image(src, tooltip, tooltip));
        buf.append("</a>");
        buf.append("</span>");
        return buf;
    }

}