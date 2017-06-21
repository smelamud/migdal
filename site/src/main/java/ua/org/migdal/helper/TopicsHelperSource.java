package ua.org.migdal.helper;

import java.io.IOException;
import java.time.LocalDateTime;

import com.github.jknack.handlebars.springmvc.HandlebarsViewResolver;
import javax.inject.Inject;
import org.springframework.context.ApplicationContext;

import com.github.jknack.handlebars.Handlebars.SafeString;
import com.github.jknack.handlebars.Options;
import com.github.jknack.handlebars.Template;

import ua.org.migdal.helper.exception.AmbiguousArgumentsException;
import ua.org.migdal.helper.util.HelperUtils;
import ua.org.migdal.util.Utils;

@HelperSource
public class TopicsHelperSource {

    @Inject
    private ApplicationContext applicationContext;

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

    public CharSequence topicsLine(Options options) throws IOException {
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
            if (Utils.biff(time)) {
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
        HelperUtils.appendMandatoryArgAttr(buf, "href", options);
        HelperUtils.appendOptionalArgAttr(buf, "title", options);
        HelperUtils.appendArgAttr(buf, "class", "topics-item", options);
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

    public CharSequence topics(Options options) throws IOException {
        String partialName = "part/" + options.get("topics");
        Template template = options.partial(partialName);
        if (template == null) {
            template = applicationContext.getBean(HandlebarsViewResolver.class).getHandlebars().compile(partialName);
            options.partial(partialName, template);
        }
        return new SafeString(options.apply(template));
    }

}