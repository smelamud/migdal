package ua.org.migdal.helper;

import java.util.List;

import javax.inject.Inject;

import com.github.jknack.handlebars.Handlebars.SafeString;
import com.github.jknack.handlebars.Options;

import ua.org.migdal.data.IdNameProjection;
import ua.org.migdal.helper.util.HelperUtils;
import ua.org.migdal.manager.TopicManager;

@HelperSource
public class TopicHelperSource {

    @Inject
    private FormsHelperSource formsHelperSource;

    @Inject
    private TopicManager topicManager;

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

}