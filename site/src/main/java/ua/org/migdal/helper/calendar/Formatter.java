package ua.org.migdal.helper.calendar;

import java.time.LocalDateTime;
import java.time.temporal.ChronoUnit;
import java.util.Locale;

import com.ibm.icu.text.DateFormatSymbols;
import com.ibm.icu.text.SimpleDateFormat;
import com.ibm.icu.util.Calendar;
import com.ibm.icu.util.HebrewCalendar;

import ua.org.migdal.util.Utils;

public class Formatter {

    public static String format(CalendarType calendarType, String pattern, LocalDateTime dateTime) {
        SimpleDateFormat dateFormat;
        DateFormatSymbols dateFormatSymbols;

        switch (calendarType) {
            case GREGORIAN_EN:
                dateFormat = new SimpleDateFormat(pattern, Locale.ENGLISH);
                break;

            case GREGORIAN_RU_NOM_PC:
                dateFormatSymbols = new DateFormatSymbols(Locale.forLanguageTag("ru"));
                dateFormatSymbols.setWeekdays(Tables.GREGORIAN_DOW_RU_PC_LONG);
                dateFormatSymbols.setShortWeekdays(Tables.GREGORIAN_DOW_RU_PC_SHORT);
                dateFormatSymbols.setMonths(Tables.GREGORIAN_MONTH_RU_NOM_PC_LONG);
                dateFormatSymbols.setShortMonths(Tables.GREGORIAN_MONTH_RU_PC_SHORT);
                dateFormat = new SimpleDateFormat(pattern, dateFormatSymbols);
                break;

            case GREGORIAN_RU_GEN_PC:
                dateFormatSymbols = new DateFormatSymbols(Locale.forLanguageTag("ru"));
                dateFormatSymbols.setWeekdays(Tables.GREGORIAN_DOW_RU_PC_LONG);
                dateFormatSymbols.setShortWeekdays(Tables.GREGORIAN_DOW_RU_PC_SHORT);
                dateFormatSymbols.setMonths(Tables.GREGORIAN_MONTH_RU_GEN_PC_LONG);
                dateFormatSymbols.setShortMonths(Tables.GREGORIAN_MONTH_RU_PC_SHORT);
                dateFormat = new SimpleDateFormat(pattern, dateFormatSymbols);
                break;

            case GREGORIAN_RU_NOM_LC:
                dateFormatSymbols = new DateFormatSymbols(Locale.forLanguageTag("ru"));
                dateFormatSymbols.setWeekdays(Tables.GREGORIAN_DOW_RU_LC_LONG);
                dateFormatSymbols.setShortWeekdays(Tables.GREGORIAN_DOW_RU_LC_SHORT);
                dateFormatSymbols.setMonths(Tables.GREGORIAN_MONTH_RU_NOM_LC_LONG);
                dateFormatSymbols.setShortMonths(Tables.GREGORIAN_MONTH_RU_LC_SHORT);
                dateFormat = new SimpleDateFormat(pattern, dateFormatSymbols);
                break;

            case GREGORIAN_RU_GEN_LC:
                dateFormat = new SimpleDateFormat(pattern, Locale.forLanguageTag("ru"));
                break;

            case JEWISH_EN:
                dateFormatSymbols = new DateFormatSymbols(Locale.ENGLISH);
                dateFormatSymbols.setWeekdays(Tables.JEWISH_DOW_EN_LONG);
                dateFormatSymbols.setShortWeekdays(Tables.JEWISH_DOW_EN_SHORT);
                dateFormatSymbols.setMonths(Tables.JEWISH_MONTH_EN_LONG);
                dateFormatSymbols.setShortMonths(Tables.JEWISH_MONTH_EN_SHORT);
                dateFormat = new SimpleDateFormat(pattern, dateFormatSymbols);
                dateFormat.setCalendar(new HebrewCalendar(Locale.ENGLISH));
                break;

            case JEWISH_RU_NOM_PC:
                dateFormatSymbols = new DateFormatSymbols(Locale.forLanguageTag("ru"));
                dateFormatSymbols.setWeekdays(Tables.JEWISH_DOW_RU_PC_LONG);
                dateFormatSymbols.setShortWeekdays(Tables.JEWISH_DOW_RU_PC_SHORT);
                dateFormatSymbols.setMonths(Tables.JEWISH_MONTH_RU_NOM_PC_LONG);
                dateFormatSymbols.setShortMonths(Tables.JEWISH_MONTH_RU_PC_SHORT);
                dateFormat = new SimpleDateFormat(pattern, dateFormatSymbols);
                dateFormat.setCalendar(new HebrewCalendar(Locale.forLanguageTag("ru")));
                break;

            case JEWISH_RU_GEN_PC:
                dateFormatSymbols = new DateFormatSymbols(Locale.forLanguageTag("ru"));
                dateFormatSymbols.setWeekdays(Tables.JEWISH_DOW_RU_PC_LONG);
                dateFormatSymbols.setShortWeekdays(Tables.JEWISH_DOW_RU_PC_SHORT);
                dateFormatSymbols.setMonths(Tables.JEWISH_MONTH_RU_GEN_PC_LONG);
                dateFormatSymbols.setShortMonths(Tables.JEWISH_MONTH_RU_PC_SHORT);
                dateFormat = new SimpleDateFormat(pattern, dateFormatSymbols);
                dateFormat.setCalendar(new HebrewCalendar(Locale.forLanguageTag("ru")));
                break;

            case JEWISH_RU_NOM_LC:
                dateFormatSymbols = new DateFormatSymbols(Locale.forLanguageTag("ru"));
                dateFormatSymbols.setWeekdays(Tables.JEWISH_DOW_RU_LC_LONG);
                dateFormatSymbols.setShortWeekdays(Tables.JEWISH_DOW_RU_LC_SHORT);
                dateFormatSymbols.setMonths(Tables.JEWISH_MONTH_RU_NOM_LC_LONG);
                dateFormatSymbols.setShortMonths(Tables.JEWISH_MONTH_RU_LC_SHORT);
                dateFormat = new SimpleDateFormat(pattern, dateFormatSymbols);
                dateFormat.setCalendar(new HebrewCalendar(Locale.forLanguageTag("ru")));
                break;

            case JEWISH_RU_GEN_LC:
                dateFormatSymbols = new DateFormatSymbols(Locale.forLanguageTag("ru"));
                dateFormatSymbols.setWeekdays(Tables.JEWISH_DOW_RU_LC_LONG);
                dateFormatSymbols.setShortWeekdays(Tables.JEWISH_DOW_RU_LC_SHORT);
                dateFormatSymbols.setMonths(Tables.JEWISH_MONTH_RU_GEN_LC_LONG);
                dateFormatSymbols.setShortMonths(Tables.JEWISH_MONTH_RU_LC_SHORT);
                dateFormat = new SimpleDateFormat(pattern, dateFormatSymbols);
                dateFormat.setCalendar(new HebrewCalendar(Locale.forLanguageTag("ru")));
                break;

            default:
                throw new IllegalArgumentException("Unknown calendar type:" + calendarType);
        }
        Calendar calendar = Calendar.getInstance();
        Utils.copyToCalendar(dateTime, calendar);
        return dateFormat.format(calendar);
    }

    private static String[] getMonths(CalendarType calendarType) {
        switch (calendarType) {
            case GREGORIAN_EN:
                return Tables.GREGORIAN_MONTH_EN;

            case GREGORIAN_RU_NOM_PC:
                return Tables.GREGORIAN_MONTH_RU_NOM_PC_LONG;

            case GREGORIAN_RU_GEN_PC:
                return Tables.GREGORIAN_MONTH_RU_GEN_PC_LONG;

            case GREGORIAN_RU_NOM_LC:
                return Tables.GREGORIAN_MONTH_RU_NOM_LC_LONG;

            case GREGORIAN_RU_GEN_LC:
                return Tables.GREGORIAN_MONTH_RU_GEN_LC_LONG;

            case JEWISH_EN:
                return Tables.JEWISH_MONTH_EN_LONG;

            case JEWISH_RU_NOM_PC:
                return Tables.JEWISH_MONTH_RU_NOM_PC_LONG;

            case JEWISH_RU_GEN_PC:
                return Tables.JEWISH_MONTH_RU_GEN_PC_LONG;

            case JEWISH_RU_NOM_LC:
                return Tables.JEWISH_MONTH_RU_NOM_LC_LONG;

            case JEWISH_RU_GEN_LC:
                return Tables.JEWISH_MONTH_RU_GEN_LC_LONG;
        }
        throw new IllegalArgumentException("Unknown calendar type:" + calendarType);
    }

    public static String formatMonth(CalendarType calendarType, short month) {
        String[] months = getMonths(calendarType);
        if (month <= 0 || month > months.length) {
            throw new IllegalArgumentException(
                    "Invalid month number " + month + " for calendar " + calendarType.name());
        }
        return months[month - 1];
    }

    public static String formatFuzzyTimeElapsed(LocalDateTime dateTime) {
        /* FIXME what to do with English? */
        long diff = dateTime.until(LocalDateTime.now(), ChronoUnit.SECONDS);
        if (diff < 60) {
            return "только что";
        }
        diff /= 60;
        if (diff == 1) {
            return "минуту назад";
        }
        if (diff < 60) {
            return String.format("%d %s назад", diff, Utils.plural(diff, new String[]{" минуту", " минуты", " минут"}));
        }
        diff /= 60;
        if (diff == 1) {
            return "час назад";
        }
        if (diff < 24) {
            return String.format("%d %s назад", diff, Utils.plural(diff, new String[]{" час", " часа", " часов"}));
        }
        diff /= 24;
        if (diff == 1) {
            return "вчера";
        }
        if (diff == 2) {
            return "позавчера";
        }
        if (diff < 30) {
            return String.format("%d %s назад", diff, Utils.plural(diff, new String[]{" день", " дня", " дней"}));
        }
        if (diff < 60) {
            return "два месяца назад";
        }
        if (diff < 90) {
            return "три месяца назад";
        }
        if (dateTime.getYear() == LocalDateTime.now().getYear()) {
            return format(CalendarType.GREGORIAN_RU_GEN_LC, "d MMMM", dateTime);
        }
        return format(CalendarType.GREGORIAN_RU_GEN_LC, "d MMMM yyyy г.", dateTime);
    }

}