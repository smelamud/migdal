package ua.org.migdal.helper;

import java.io.IOException;
import java.time.LocalDateTime;

import javax.inject.Inject;

import org.springframework.util.StringUtils;

import com.github.jknack.handlebars.Handlebars.SafeString;
import com.github.jknack.handlebars.Options;

import ua.org.migdal.data.Entry;
import ua.org.migdal.data.Posting;
import ua.org.migdal.helper.calendar.CalendarType;
import ua.org.migdal.helper.calendar.Formatter;
import ua.org.migdal.helper.util.HelperUtils;
import ua.org.migdal.session.RequestContext;
import ua.org.migdal.util.Utils;

@HelperSource
public class PostingHelperSource {

    @Inject
    private RequestContext requestContext;

    @Inject
    private ImagesHelperSource imagesHelperSource;

    @Inject
    private VoteHelperSource voteHelperSource;

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
                buf.append("<a class=\"name\" href=\"/users/");
                buf.append(HelperUtils.ue(entry.getUserFolder()));
                buf.append("/\"");
                HelperUtils.appendAttr(buf, "data-id", entry.getUser().getId());
                buf.append('>');
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

    public CharSequence topicLink(Posting posting, Options options) {
        StringBuilder buf = new StringBuilder();
        buf.append("<a class=\"posting-topic\"");
        HelperUtils.appendAttr(buf, "href", posting.getGrpGeneralHref());
        buf.append('>');
        HelperUtils.safeAppend(buf, posting.getGrpGeneralTitle());
        buf.append("</a>");
        return new SafeString(buf);
    }

    public CharSequence printLink(Posting posting) {
        StringBuilder buf = new StringBuilder();
        buf.append("<span>");
        buf.append(imagesHelperSource.image("/pics/print.gif", null, null, "position: relative; top: 1px", null));
        buf.append("&nbsp;");
        buf.append("<a target=\"_blank\"");
        String href = String.format("%s?print=1", posting.getGrpDetailsHref());
        HelperUtils.appendAttr(buf, "href", href);
        buf.append('>');
        if (!requestContext.isEnglish()) {
            buf.append("Для печати");
        } else {
            buf.append("Print version");
        }
        buf.append("</a>");
        buf.append("</span>");
        return new SafeString(buf);
    }

    public CharSequence editLink(Posting posting) {
        StringBuilder buf = new StringBuilder();
        buf.append("<a");
        String href = String.format("%sedit/?back=%s",
                posting.getGrpDetailsHref(), HelperUtils.ue(requestContext.getLocation()));
        HelperUtils.appendAttr(buf, "href", href);
        buf.append('>');
        if (!requestContext.isEnglish()) {
            buf.append("Изменить");
        } else {
            buf.append("Edit");
        }
        buf.append("</a>");
        return new SafeString(buf);
    }

    public CharSequence discussLink(Posting posting) {
        if (requestContext.isEnglish()) {
            return "";
        }

        StringBuilder buf = new StringBuilder();
        buf.append("<span>");
        if (posting.getAnswers() > 0) {
            buf.append("<a");
            String href = String.format("%sdiscuss/", posting.getGrpDetailsHref());
            HelperUtils.appendAttr(buf, "href", href);
            buf.append('>');
            buf.append(posting.getAnswers());
            buf.append(' ');
            buf.append(Utils.plural(posting.getAnswers(), new String[]{"комментарий", "комментария", "комментариев"}));
        } else {
            buf.append("<a");
            String href = String.format("%sdiscuss/add/", posting.getGrpDetailsHref());
            HelperUtils.appendAttr(buf, "href", href);
            buf.append('>');
            buf.append("Оставить комментарий");
        }
        buf.append("</a>");
        buf.append("&nbsp;");
        buf.append(imagesHelperSource.image("/pics/discussion.gif"));
        buf.append("</span>");
        return new SafeString(buf);
    }

    public CharSequence postingControls(Options options) {
        if (requestContext.isPrintMode()) {
            return "";
        }

        Posting posting = HelperUtils.mandatoryHash("posting", options);
        boolean showPrint = HelperUtils.boolArg(options.hash("showPrint", "true"));
        boolean showEdit = HelperUtils.boolArg(options.hash("showEdit", "true"));
        boolean showDiscuss = HelperUtils.boolArg(options.hash("showDiscuss"));

        if (!showPrint && !showEdit && !showDiscuss) {
            return "";
        }

        StringBuilder buf = new StringBuilder();
        buf.append("<div class=\"posting-bottom\">");
        if (showPrint) {
            buf.append(printLink(posting));
            buf.append("&nbsp;&nbsp;");
        }
        if (showEdit && posting.isWritable()) {
            buf.append(editLink(posting));
            buf.append("&nbsp;&nbsp;");
        }
        if (showDiscuss) {
            buf.append("<div class=\"right\">");
            buf.append(discussLink(posting));
            buf.append("</div>");
        }
        buf.append("<div class=\"clear-floats\"></div>");
        buf.append("</div>");
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
        CharSequence titleLarge = options.hash("titleLarge");
        long fixedWidth = HelperUtils.intArg("fixedWidth", options.hash("fixedWidth"));
        long fixedHeight = HelperUtils.intArg("fixedHeight", options.hash("fixedHeight"));
        boolean enlargeAlways = HelperUtils.boolArg(options.hash("enlargeAlways"));
        CharSequence href = options.hash("href");
        CharSequence editHref = options.hash("editHref");
        CharSequence rmHref = options.hash("rmHref");
        boolean controls = HelperUtils.boolArg(options.hash("controls"));
        boolean hollow = HelperUtils.boolArg(options.hash("hollow"));

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
                buf.append("<a class=\"enlargeable\"");
                HelperUtils.appendAttr(buf, "href", posting.getLargeImageUrl());
                if (titleLargeId > 0) {
                    HelperUtils.appendAttr(buf, "data-title-large-id", titleLargeId);
                } else if (!StringUtils.isEmpty(titleLarge)) {
                    HelperUtils.appendAttr(buf, "data-title-large", titleLarge);
                }
                HelperUtils.appendAttr(buf, "rel", rel);
                buf.append('>');
            } else {
                buf.append("<a");
                HelperUtils.appendAttr(buf, "href", href);
                buf.append(">");
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
                buf.append("<a");
                HelperUtils.appendAttr(buf, "href", href);
                buf.append(">");
            }
            buf.append(imageTag(posting.getImageUrl(), posting.getImageX(), posting.getImageY(), imageTitle));
            if (!StringUtils.isEmpty(href)) {
                buf.append("</a>");
            }
        }
        if (!hollow && controls && (!StringUtils.isEmpty(editHref) || !StringUtils.isEmpty(rmHref))) {
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
        if (!hollow && controls) {
            String style = String.format("top: %dpx", sizeY - 25);
            buf.append(voteHelperSource.rating((long) posting.getRating(), style, posting.getId()));
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
        buf.append("<img");
        HelperUtils.appendAttr(buf, "width", sizeX);
        HelperUtils.appendAttr(buf, "height", sizeY);
        if (!StringUtils.isEmpty(title)) {
            HelperUtils.appendAttr(buf, "alt", title);
            HelperUtils.appendAttr(buf, "title", title);
        }
        HelperUtils.appendAttr(buf, "src", src);
        buf.append('>');
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
        buf.append("<a");
        HelperUtils.appendAttr(buf, "href", href);
        buf.append('>');
        buf.append(imagesHelperSource.image(src, tooltip, tooltip));
        buf.append("</a>");
        buf.append("</span>");
        return buf;
    }

    public CharSequence postingPartial(Posting posting, Options options) throws IOException {
        return "part/" + posting.getGrpPartial();
    }

}