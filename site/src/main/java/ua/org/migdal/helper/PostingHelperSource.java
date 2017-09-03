package ua.org.migdal.helper;

import java.time.LocalDateTime;

import com.github.jknack.handlebars.Handlebars.SafeString;
import com.github.jknack.handlebars.Options;
import ua.org.migdal.helper.calendar.CalendarType;
import ua.org.migdal.helper.calendar.Formatter;
import ua.org.migdal.helper.util.HelperUtils;

@HelperSource
public class PostingHelperSource {

    public CharSequence sentView(Options options) {
        LocalDateTime timestamp = HelperUtils.timestampArg("date", options.hash("date"));
        return new SafeString(Formatter.format(CalendarType.GREGORIAN_EN, "dd.MM.yyyy'&nbsp;'HH:mm", timestamp));
    }

}