package ua.org.migdal.helper;

import java.time.LocalDateTime;

import javax.inject.Inject;

import com.github.jknack.handlebars.Handlebars.SafeString;
import com.github.jknack.handlebars.Options;
import ua.org.migdal.data.Entry;
import ua.org.migdal.helper.calendar.CalendarType;
import ua.org.migdal.helper.calendar.Formatter;
import ua.org.migdal.helper.util.HelperUtils;
import ua.org.migdal.session.RequestContext;

@HelperSource
public class PostingHelperSource {

    @Inject
    private RequestContext requestContext;

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

}