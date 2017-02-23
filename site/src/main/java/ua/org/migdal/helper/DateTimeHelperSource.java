package ua.org.migdal.helper;

import java.sql.Date;
import java.sql.Timestamp;
import java.time.Instant;
import java.time.LocalDate;
import java.time.LocalDateTime;
import java.time.ZoneId;
import java.time.ZonedDateTime;
import java.time.format.DateTimeFormatter;
import java.time.format.DateTimeParseException;
import java.time.temporal.ChronoUnit;

import com.github.jknack.handlebars.Options;

import ua.org.migdal.helper.calendar.CalendarType;
import ua.org.migdal.helper.calendar.Formatter;
import ua.org.migdal.helper.exception.DateFormatException;
import ua.org.migdal.helper.exception.TypeMismatchException;

@HelperSource
public class DateTimeHelperSource {

    public CharSequence now() {
        return Long.toString(System.currentTimeMillis());
    }

    private CalendarType calendarTypeArg(String type) {
        type = type.replace('-', '_').toUpperCase();
        try {
            return CalendarType.valueOf(type);
        } catch (IllegalArgumentException e) {
            throw new TypeMismatchException(0, CalendarType.class, type);
        }
    }

    private LocalDateTime timestampArg(String paramName, Object value) {
        if (value == null) {
            return LocalDateTime.now();
        }
        if (value instanceof Instant) {
            return ((Instant) value).atZone(ZoneId.systemDefault()).toLocalDateTime();
        }
        if (value instanceof LocalDate) {
            return ((LocalDate) value).atStartOfDay();
        }
        if (value instanceof LocalDateTime) {
            return (LocalDateTime) value;
        }
        if (value instanceof ZonedDateTime) {
            return ((ZonedDateTime) value).toLocalDateTime();
        }
        if (value instanceof Date) {
            return ((Date) value).toInstant().atZone(ZoneId.systemDefault()).toLocalDateTime();
        }
        if (value instanceof Timestamp) {
            return ((Timestamp) value).toLocalDateTime();
        }
        return Instant.ofEpochMilli(HelperUtils.intArg(paramName, value))
                .atZone(ZoneId.systemDefault())
                .toLocalDateTime();
    }

    public CharSequence cal(String type, String pattern, Options options) {
        CalendarType calendarType = calendarTypeArg(type);
        LocalDateTime timestamp = timestampArg("date", options.hash("date"));
        return Formatter.format(calendarType, pattern, timestamp);
    }

    public CharSequence month(String type, Object monthN) {
        CalendarType calendarType = calendarTypeArg(type);
        short n = (short) HelperUtils.intArg(1, monthN);
        return Formatter.formatMonth(calendarType, n);
    }

    public CharSequence daysTill(String var, String date, Options options) {
        try {
            LocalDate till = LocalDate.parse(date, DateTimeFormatter.ISO_LOCAL_DATE);
            long days = LocalDate.now().until(till, ChronoUnit.DAYS);
            options.data(var, Long.toString(days));
        } catch (DateTimeParseException e) {
            throw new DateFormatException(1, DateTimeFormatter.ISO_LOCAL_DATE.toString(), date);
        }
        return "";
    }

    public CharSequence fuzzy(Options options) {
        LocalDateTime timestamp = timestampArg("date", options.hash("date"));
        return Formatter.formatFuzzyTimeElapsed(timestamp);
    }

}