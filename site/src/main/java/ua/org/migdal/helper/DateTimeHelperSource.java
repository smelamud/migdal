package ua.org.migdal.helper;

import java.util.Date;

import com.github.jknack.handlebars.Options;

import ua.org.migdal.helper.calendar.CalendarType;
import ua.org.migdal.helper.calendar.Formatter;
import ua.org.migdal.helper.exception.TypeMismatchException;

@HelperSource
public class DateTimeHelperSource {

    public CharSequence now() {
        return Long.toString(System.currentTimeMillis());
    }

    public CharSequence cal(String type, String pattern, Options options) {
        type = type.replace('-', '_').toUpperCase();
        CalendarType calendarType = null;
        try {
            calendarType = CalendarType.valueOf(type);
        } catch (IllegalArgumentException e) {
            throw new TypeMismatchException("0", CalendarType.class, type);
        }
        String dateString = options.hash("date");
        Date timestamp = dateString != null ? new Date(Long.parseLong(dateString)) : new Date();
        return Formatter.format(calendarType, pattern, timestamp);
    }

}
