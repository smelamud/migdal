package ua.org.migdal.helper;

import java.util.List;

import javax.inject.Inject;

import com.github.jknack.handlebars.Handlebars.SafeString;
import com.github.jknack.handlebars.Options;

import org.springframework.util.StringUtils;
import ua.org.migdal.data.IdNameProjection;
import ua.org.migdal.helper.util.HelperUtils;

@HelperSource
public class TopicHelperSource {

    @Inject
    private FormsHelperSource formsHelperSource;

    public CharSequence topicSelect(Options options) {
        List<IdNameProjection> list = HelperUtils.mandatoryHash("list", options);
        String name = HelperUtils.mandatoryHash("name", options);
        long value = HelperUtils.intArg("value", options.hash("value", 0));
        boolean mandatory = HelperUtils.boolArg(options.hash("mandatory", false));
        String nullTitle = options.hash("nullTitle", mandatory ? "Выберите тему" : "Без темы");

        StringBuilder buf = new StringBuilder();
        buf.append("<select name=\"");
        buf.append(name);
        buf.append("\">");
        buf.append(formsHelperSource.selectOption(0, value <= 0, null, String.format("-- %s --", nullTitle)));
        for (IdNameProjection topic : list) {
            buf.append(formsHelperSource.selectOption(topic.getId(), value == topic.getId(), null,
                                                      topic.getNameShort()));
        }
        buf.append("</select>");
        return new SafeString(buf);
    }

    public CharSequence formTopicSelect(Options options) {
        List<IdNameProjection> list = HelperUtils.mandatoryHash("list", options);
        CharSequence title = HelperUtils.mandatoryHash("title", options);
        boolean mandatory = HelperUtils.boolArg(options.hash("mandatory", false));
        CharSequence comment = options.hash("comment", "");
        String name = HelperUtils.mandatoryHash("name", options);
        long value = HelperUtils.intArg("value", options.hash("value", 0));
        String nullTitle = options.hash("nullTitle", mandatory ? "Выберите тему" : "Без темы");

        if (list.size() == 0) {
            return formsHelperSource.hidden(name, value, null);
        }
        if (list.size() == 1 && mandatory) {
            IdNameProjection topic = list.get(0);
            return formsHelperSource.hidden(name, topic.getId(), null);
        }

        StringBuilder buf = new StringBuilder();
        buf.append(formsHelperSource.formSelectBegin(options, title, mandatory, comment, name, "list", false, null));
        buf.append(formsHelperSource.formOption(options, String.format("-- %s --", nullTitle), 0, value <= 0));
        for (IdNameProjection topic : list) {
            buf.append(formsHelperSource.formOption(options, topic.getNameShort(), topic.getId(),
                                                    value == topic.getId()));
        }
        buf.append(formsHelperSource.formSelectEnd("list"));
        return new SafeString(buf);
    }

    public CharSequence upperLevel(Options options) {
        CharSequence href = HelperUtils.mandatoryHash("href", options);
        CharSequence title = options.hash("title");

        if (StringUtils.isEmpty(title)) {
            title = "К разделу «name»";
        }

        StringBuilder buf = new StringBuilder();
        buf.append("<div class=\"up-level\">");
        buf.append("<a href=\"");
        HelperUtils.safeAppend(buf, href);
        buf.append("\">");
        HelperUtils.safeAppend(buf, title);
        buf.append("</a>");
        buf.append("</div>");
        return new SafeString(buf);
    }

}