package ua.org.migdal.helper;

import java.io.IOException;
import java.time.LocalDateTime;

import javax.inject.Inject;

import org.springframework.util.StringUtils;

import com.github.jknack.handlebars.Handlebars.SafeString;
import com.github.jknack.handlebars.Options;

import ua.org.migdal.helper.exception.AmbiguousArgumentsException;
import ua.org.migdal.helper.util.HelperUtils;
import ua.org.migdal.util.Utils;

@HelperSource
public class TopicsHelperSource {

    @Inject
    private VoteHelperSource voteHelperSource;

    public CharSequence topicsTable(Options options) throws IOException {
        CharSequence title = options.hash("title");
        CharSequence cls = options.hash("class");

        StringBuilder buf = new StringBuilder();
        if (title != null && title.length() > 0) {
            buf.append("<div class=\"topics-header\">");
            HelperUtils.safeAppend(buf, title);
            buf.append("</div>");
        }
        buf.append("<ul class=\"topics");
        if (cls != null && cls.length() > 0) {
            buf.append(' ');
            HelperUtils.safeAppend(buf, cls);
        }
        buf.append("\">");
        buf.append(options.apply(options.fn));
        buf.append("</ul>");
        return new SafeString(buf);
    }

    public CharSequence topicsSubtable(Options options) throws IOException {
        CharSequence cls = options.hash("class");

        String tableClass = StringUtils.isEmpty(cls) ? "topics" : "topics " + cls;

        StringBuilder buf = new StringBuilder();
        buf.append("<li class=\"topics-subtable\">");
        buf.append("<ul class=\"");
        buf.append(tableClass);
        buf.append("\">");
        buf.append(options.apply(options.fn));
        buf.append("</ul>");
        buf.append("</li>");
        return new SafeString(buf);
    }

    public CharSequence topicsLine(Options options) throws IOException {
        CharSequence href = HelperUtils.mandatoryHash("href", options);
        CharSequence title = options.hash("title");
        CharSequence klass = options.hash("class", "topics-item");

        boolean arrow;
        Object index = options.hash("index");
        if (index == null) {
            arrow = HelperUtils.boolArg(options.hash("arrow", false));
        } else {
            if (options.hash("arrow") != null) {
                throw new AmbiguousArgumentsException("arrow", "index");
            }
            arrow = index.equals(options.get("topicsIndex"));
        }
        LocalDateTime time = HelperUtils.timestampArg("time", options.hash("time"), false);
        Object text = options.hash("text", "");
        Long count = HelperUtils.integerArg("count", options.hash("count"));
        Long rating = HelperUtils.integerArg("rating", options.hash("rating"));
        boolean noBullet = HelperUtils.boolArg(options.hash("noBullet", false));

        StringBuilder buf = new StringBuilder();
        if (arrow) {
            if (Utils.biff(time)) { // FIXME deprecated?
                buf.append("<li class=\"topics-ner-arrow\">");
            } else {
                buf.append("<li class=\"topics-arrow\">");
            }
        } else {
            if (Utils.biff(time)) {
                buf.append("<li class=\"topics-ner\">");
            } else {
                if (noBullet) {
                    buf.append("<li class=\"topics-nobullet\">");
                } else {
                    buf.append("<li>");
                }
            }
        }
        buf.append("<a");
        HelperUtils.appendAttr(buf, "href", href);
        HelperUtils.appendAttr(buf, "title", title);
        HelperUtils.appendAttr(buf, "class", klass);
        buf.append('>');
        HelperUtils.safeAppend(buf, text);
        if (count != null) {
            buf.append("&nbsp;<span class=\"topics-answers\">(");
            buf.append(count);
            buf.append(")</span>");
        }
        buf.append("</a>");
        if (rating != null) {
            buf.append(voteHelperSource.rating(rating));
        }
        buf.append("</li>");
        return new SafeString(buf);
    }

    public CharSequence topicsSplitter(Options options) {
        CharSequence cls = options.hash("class");

        StringBuilder buf = new StringBuilder();
        buf.append("<li class=\"topics-nobullet");
        if (cls != null) {
            buf.append(' ');
            HelperUtils.safeAppend(buf, cls);
        }
        buf.append("\">&nbsp;</li>");
        return new SafeString(buf);
    }

    public CharSequence topicsPartial(Options options) throws IOException {
        return "part/" + options.get("topics");
    }

}