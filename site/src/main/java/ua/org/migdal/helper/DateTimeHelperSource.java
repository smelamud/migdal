package ua.org.migdal.helper;

import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.Date;

import com.github.jknack.handlebars.Options;

import ua.org.migdal.helper.calendar.CalendarType;
import ua.org.migdal.helper.calendar.Formatter;
import ua.org.migdal.helper.exception.DateFormatException;
import ua.org.migdal.helper.exception.TypeMismatchException;

@HelperSource
public class DateTimeHelperSource {

    private static final SimpleDateFormat DATE_FORMAT = new SimpleDateFormat("yyyy-MM-dd");

    public CharSequence now() {
        return Long.toString(System.currentTimeMillis());
    }

    public CharSequence cal(String type, String pattern, Options options) {
        type = type.replace('-', '_').toUpperCase();
        CalendarType calendarType = null;
        try {
            calendarType = CalendarType.valueOf(type);
        } catch (IllegalArgumentException e) {
            throw new TypeMismatchException(0, CalendarType.class, type);
        }
        String dateString = options.hash("date");
        Date timestamp = dateString != null ? new Date(Long.parseLong(dateString)) : new Date();
        return Formatter.format(calendarType, pattern, timestamp);
    }

    public CharSequence daysTill(String var, String date, Options options) {
        try {
            long till = DATE_FORMAT.parse(date).getTime();
            long now = System.currentTimeMillis();
            int days = (int)((till - now) / 1000 / 3600 / 24);
            options.data(var, Integer.toString(days));
        } catch (ParseException e) {
            throw new DateFormatException(1, DATE_FORMAT.toPattern(), date);
        }
        return "";
    }

}