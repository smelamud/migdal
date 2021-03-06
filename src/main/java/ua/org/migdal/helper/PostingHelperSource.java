package ua.org.migdal.helper;

import java.io.IOException;
import java.time.LocalDateTime;

import javax.inject.Inject;

import org.springframework.util.StringUtils;

import com.github.jknack.handlebars.Handlebars.SafeString;
import com.github.jknack.handlebars.Options;

import ua.org.migdal.data.ChatMessage;
import ua.org.migdal.data.Comment;
import ua.org.migdal.data.Entry;
import ua.org.migdal.data.Posting;
import ua.org.migdal.helper.calendar.CalendarType;
import ua.org.migdal.helper.calendar.Formatter;
import ua.org.migdal.helper.util.HelperUtils;
import ua.org.migdal.mtext.MtextConverter;
import ua.org.migdal.session.RequestContext;
import ua.org.migdal.util.Utils;

@HelperSource
public class PostingHelperSource {

    @Inject
    private RequestContext requestContext;

    @Inject
    private MtextConverter mtextConverter;

    @Inject
    private ImagesHelperSource imagesHelperSource;

    @Inject
    private UsersHelperSource usersHelperSource;

    @Inject
    private VoteHelperSource voteHelperSource;

    public CharSequence sentView(Options options) {
        LocalDateTime timestamp = HelperUtils.timestampArg("date", options.hash("date"));
        Posting posting = options.hash("posting");
        Comment comment = options.hash("comment");

        if (comment != null) {
            return sentView(posting, comment);
        } else if (posting != null) {
            return sentView(posting);
        } else {
            return sentView(timestamp);
        }
    }

    CharSequence sentView(Posting posting) {
        StringBuilder buf = new StringBuilder();
        buf.append("<a");
        HelperUtils.appendAttr(buf, "href", posting.getGrpDetailsHref());
        HelperUtils.appendAttr(buf, "title", "Ссылка на " + posting.getGrpWhat());
        buf.append('>');
        buf.append(sentView(posting.getSent().toLocalDateTime()));
        buf.append("</a>");
        return new SafeString(buf);
    }

    CharSequence sentView(Posting posting, Comment comment) {
        StringBuilder buf = new StringBuilder();
        buf.append("<a");
        HelperUtils.appendAttr(buf, "href", posting.getGrpDetailsHref() + comment.getQuery());
        HelperUtils.appendAttr(buf, "title", "Ссылка на комментарий");
        buf.append('>');
        buf.append(sentView(comment.getSent().toLocalDateTime()));
        buf.append("</a>");
        return new SafeString(buf);
    }

    CharSequence sentView(LocalDateTime timestamp) {
        return new SafeString(Formatter.format(CalendarType.GREGORIAN_EN, "dd.MM.yyyy'&nbsp;'HH:mm", timestamp));
    }

    public CharSequence senderLink(Object object) {
        if (requestContext.isEnglish()) {
            return "";
        }

        if (object instanceof Entry) {
            Entry entry = (Entry) object;
            return usersHelperSource.userLink(entry.getUser(), entry.getGuestLogin());
        }
        if (object instanceof ChatMessage) {
            ChatMessage message = (ChatMessage) object;
            return usersHelperSource.userLink(message.getSender(), message.getGuestLogin());
        }
        return "";
    }

    public CharSequence topicLink(Posting posting, Options options) {
        CharSequence suffix = options.hash("suffix");

        StringBuilder buf = new StringBuilder();
        buf.append("<a class=\"posting-topic\"");
        String href = posting.getGrpGeneralHref();
        if (suffix != null) {
            href = href + suffix;
        }
        HelperUtils.appendAttr(buf, "href", href);
        buf.append('>');
        HelperUtils.safeAppend(buf, posting.getGrpGeneralTitle());
        buf.append("</a>");
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

    public CharSequence commentLink(Posting posting) {
        if (requestContext.isEnglish()) {
            return "";
        }

        StringBuilder buf = new StringBuilder();
        buf.append("<span>");
        if (posting.getComments() > 0) {
            buf.append("<a");
            String href = String.format("%s#comments", posting.getGrpDetailsHref());
            HelperUtils.appendAttr(buf, "href", href);
            buf.append('>');
            buf.append(posting.getComments());
            buf.append(' ');
            buf.append(Utils.plural(posting.getComments(), new String[]{"комментарий", "комментария", "комментариев"}));
        } else {
            buf.append("<a");
            String href = String.format("%s#comment-add", posting.getGrpDetailsHref());
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
        Posting posting = HelperUtils.mandatoryHash("posting", options);
        boolean showEdit = HelperUtils.boolArg(options.hash("showEdit", "true"));
        boolean editable = posting.isWritable() && !posting.isShadow();
        boolean showComment = HelperUtils.boolArg(options.hash("showComment"));

        if (!(showEdit && editable) && !showComment) {
            return new SafeString("<div class=\"clear-floats\"></div>");
        }

        StringBuilder buf = new StringBuilder();
        buf.append("<div class=\"posting-bottom\">");
        if (showEdit && editable) {
            buf.append(editLink(posting));
            buf.append("&nbsp;&nbsp;&nbsp;");
        }
        if (showComment) {
            buf.append("<div class=\"right\">");
            buf.append(commentLink(posting));
            buf.append("</div>");
        }
        buf.append("<div class=\"clear-floats\"></div>");
        buf.append("</div>");
        buf.append("<div class=\"clear-floats\"></div>");
        return new SafeString(buf);
    }

    public CharSequence postingImage(Options options) {
        Posting posting = HelperUtils.mandatoryHash("posting", options);
        CharSequence align = options.hash("align");
        boolean noClear = HelperUtils.boolArg(options.hash("noClear"));
        boolean noMargin = HelperUtils.boolArg(options.hash("noMargin"));
        Object imageTitle = options.hash("imageTitle");
        CharSequence galleryId = options.hash("galleryId");
        Object title = options.hash("title");
        CharSequence titleLargeId = options.hash("titleLargeId");
        CharSequence titleLarge = options.hash("titleLarge");
        long fixedWidth = HelperUtils.intArg("fixedWidth", options.hash("fixedWidth"));
        long fixedHeight = HelperUtils.intArg("fixedHeight", options.hash("fixedHeight"));
        boolean enlargeAlways = HelperUtils.boolArg(options.hash("enlargeAlways"));
        CharSequence href = options.hash("href");
        CharSequence editHref = options.hash("editHref");
        CharSequence rmHref = options.hash("rmHref");
        boolean controls = HelperUtils.boolArg(options.hash("controls"));
        boolean hollow = HelperUtils.boolArg(options.hash("hollow"));

        return entryImage(posting, align, noClear, noMargin, imageTitle, galleryId, title, titleLargeId, titleLarge,
                fixedWidth, fixedHeight, enlargeAlways, href, editHref, rmHref, controls, hollow);
    }

    public CharSequence picture(Options options) {
        Posting posting = HelperUtils.mandatoryHash("posting", options);
        CharSequence align = options.hash("align");
        boolean noClear = HelperUtils.boolArg(options.hash("noClear"));
        boolean noMargin = HelperUtils.boolArg(options.hash("noMargin"));
        CharSequence galleryId = options.hash("galleryId");
        Object title = options.hash("title");
        String titleLargeId = String.format("picture-controls-%d", posting.getId());
        long fixedWidth = HelperUtils.intArg("fixedWidth", options.hash("fixedWidth"));
        long fixedHeight = HelperUtils.intArg("fixedHeight", options.hash("fixedHeight"));
        String editHref = posting.isHasSmallImage() && posting.isWritable() ? "auto" : "";
        boolean hollow = HelperUtils.boolArg(options.hash("hollow"));

        StringBuilder buf = new StringBuilder();
        buf.append(entryImage(posting, align, noClear, noMargin, null, galleryId, title, titleLargeId, null,
                fixedWidth, fixedHeight, true, null, editHref, null, true, hollow));
        buf.append(String.format("<div id=\"picture-controls-%d\" class=\"picture-controls\">", posting.getId()));
        buf.append(voteHelperSource.votePanel(posting.getId(), (long) posting.getRating(), "right"));
        buf.append("<span class=\"picture-title\">");
        buf.append(mtextConverter.toHtml(posting.getTitleMtext()));
        buf.append("</span>");
        if (!StringUtils.isEmpty(posting.getSource())) {
            buf.append(mtextConverter.toHtml(posting.getSourceMtext()));
        }
        buf.append("<br />");
        buf.append("<div class=\"sent\" style=\"clear: left\">");
        buf.append(sentView(posting));
        buf.append(" &nbsp; ");
        buf.append(senderLink(posting));
        buf.append("</div>");
        if (posting.isWritable() || posting.isPostable()) {
            buf.append("<div class=\"picture-bottom\">");
            if (posting.isWritable()) {
                buf.append(editLink(posting));
            }
            if (posting.isPostable()) {
                buf.append(" &nbsp; ");
                buf.append(commentLink(posting));
            }
            buf.append("</div>");
        }
        buf.append("</div>");
        return new SafeString(buf);
    }

    CharSequence entryImage(
            Entry entry,
            CharSequence align,
            boolean noClear,
            boolean noMargin,
            Object imageTitle,
            CharSequence galleryId,
            Object title,
            CharSequence titleLargeId,
            CharSequence titleLarge,
            long fixedWidth,
            long fixedHeight,
            boolean enlargeAlways,
            CharSequence href,
            CharSequence editHref,
            CharSequence rmHref,
            boolean controls,
            boolean hollow) {
        long sizeX = fixedWidth != 0 ? fixedWidth : entry.getSmallImageX();
        long sizeY = fixedHeight != 0 ? fixedHeight : entry.getSmallImageY();

        StringBuilder buf = new StringBuilder();
        buf.append("<div class=\"pic\" style=\"width: ");
        buf.append(sizeX);
        buf.append("px");
        buf.append(imageDivFloat(align, noClear, noMargin));
        buf.append("\">");
        if (entry.isHasLargeImage() || enlargeAlways) {
            if (StringUtils.isEmpty(href)) {
                buf.append("<a class=\"enlargeable\"");
                HelperUtils.appendAttr(buf, "href", entry.getLargeImageUrl());
                if (!StringUtils.isEmpty(titleLargeId)) {
                    HelperUtils.appendAttr(buf, "data-title-large-id", titleLargeId);
                } else if (!StringUtils.isEmpty(titleLarge)) {
                    HelperUtils.appendAttr(buf, "data-title-large", titleLarge);
                }
                HelperUtils.appendAttr(buf, "data-fancybox", galleryId);
                buf.append('>');
            } else {
                buf.append("<a");
                HelperUtils.appendAttr(buf, "href", href);
                buf.append(">");
            }
            if (!hollow) {
                buf.append(imageTag(entry.getSmallImageUrl(), sizeX, sizeY, imageTitle));
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
            buf.append(imageTag(entry.getImageUrl(), entry.getImageX(), entry.getImageY(), imageTitle));
            if (!StringUtils.isEmpty(href)) {
                buf.append("</a>");
            }
        }
        if (!hollow && controls && (!StringUtils.isEmpty(editHref) || !StringUtils.isEmpty(rmHref))) {
            buf.append("<div class=\"buttons\">");
            if (!StringUtils.isEmpty(editHref)) {
                if (editHref.equals("auto") && (entry instanceof Posting)) {
                    editHref = String.format("%sedit/?back=%s",
                            ((Posting) entry).getGrpDetailsHref(), requestContext.getLocation());
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
            buf.append(voteHelperSource.rating((long) entry.getRating(), style, entry.getId()));
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

    public CharSequence postingShare() {
        return new SafeString("<div class=\"addthis_inline_share_toolbox hidden-print\"></div>");
    }

    public CharSequence postingPartial(Posting posting, Options options) throws IOException {
        return "part/" + posting.getGrpPartial();
    }

}