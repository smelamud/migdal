package ua.org.migdal.helper.calendar;

import java.util.Date;
import java.util.Locale;

import com.ibm.icu.text.DateFormatSymbols;
import com.ibm.icu.text.SimpleDateFormat;
import com.ibm.icu.util.HebrewCalendar;

public class Formatter {

    public static String format(CalendarType calendarType, String pattern, Date timestamp) {
        SimpleDateFormat dateFormat;
        DateFormatSymbols dateFormatSymbols;

        switch (calendarType) {
            case GREGORIAN_EN:
                dateFormat = new SimpleDateFormat(pattern, Locale.ENGLISH);
                return dateFormat.format(timestamp);

            case GREGORIAN_RU_NOM_PC:
                dateFormatSymbols = new DateFormatSymbols(Locale.forLanguageTag("ru"));
                dateFormatSymbols.setWeekdays(Tables.GREGORIAN_DOW_RU_PC_LONG);
                dateFormatSymbols.setShortWeekdays(Tables.GREGORIAN_DOW_RU_PC_SHORT);
                dateFormatSymbols.setMonths(Tables.GREGORIAN_MONTH_RU_NOM_PC_LONG);
                dateFormatSymbols.setShortMonths(Tables.GREGORIAN_MONTH_RU_PC_SHORT);
                dateFormat = new SimpleDateFormat(pattern, dateFormatSymbols);
                return dateFormat.format(timestamp);

            case GREGORIAN_RU_GEN_PC:
                dateFormatSymbols = new DateFormatSymbols(Locale.forLanguageTag("ru"));
                dateFormatSymbols.setWeekdays(Tables.GREGORIAN_DOW_RU_PC_LONG);
                dateFormatSymbols.setShortWeekdays(Tables.GREGORIAN_DOW_RU_PC_SHORT);
                dateFormatSymbols.setMonths(Tables.GREGORIAN_MONTH_RU_GEN_PC_LONG);
                dateFormatSymbols.setShortMonths(Tables.GREGORIAN_MONTH_RU_PC_SHORT);
                dateFormat = new SimpleDateFormat(pattern, dateFormatSymbols);
                return dateFormat.format(timestamp);

            case GREGORIAN_RU_NOM_LC:
                dateFormatSymbols = new DateFormatSymbols(Locale.forLanguageTag("ru"));
                dateFormatSymbols.setWeekdays(Tables.GREGORIAN_DOW_RU_LC_LONG);
                dateFormatSymbols.setShortWeekdays(Tables.GREGORIAN_DOW_RU_LC_SHORT);
                dateFormatSymbols.setMonths(Tables.GREGORIAN_MONTH_RU_NOM_LC_LONG);
                dateFormatSymbols.setShortMonths(Tables.GREGORIAN_MONTH_RU_LC_SHORT);
                dateFormat = new SimpleDateFormat(pattern, dateFormatSymbols);
                return dateFormat.format(timestamp);

            case GREGORIAN_RU_GEN_LC:
                dateFormat = new SimpleDateFormat(pattern, Locale.forLanguageTag("ru"));
                return dateFormat.format(timestamp);

            case JEWISH_EN:
                dateFormatSymbols = new DateFormatSymbols(Locale.ENGLISH);
                dateFormatSymbols.setWeekdays(Tables.JEWISH_DOW_EN_LONG);
                dateFormatSymbols.setShortWeekdays(Tables.JEWISH_DOW_EN_SHORT);
                dateFormatSymbols.setMonths(Tables.JEWISH_MONTH_EN_LONG);
                dateFormatSymbols.setShortMonths(Tables.JEWISH_MONTH_EN_SHORT);
                dateFormat = new SimpleDateFormat(pattern, dateFormatSymbols);
                dateFormat.setCalendar(new HebrewCalendar(Locale.ENGLISH));
                return dateFormat.format(timestamp);

            case JEWISH_RU_NOM_PC:
                dateFormatSymbols = new DateFormatSymbols(Locale.forLanguageTag("ru"));
                dateFormatSymbols.setWeekdays(Tables.JEWISH_DOW_RU_PC_LONG);
                dateFormatSymbols.setShortWeekdays(Tables.JEWISH_DOW_RU_PC_SHORT);
                dateFormatSymbols.setMonths(Tables.JEWISH_MONTH_RU_NOM_PC_LONG);
                dateFormatSymbols.setShortMonths(Tables.JEWISH_MONTH_RU_PC_SHORT);
                dateFormat = new SimpleDateFormat(pattern, dateFormatSymbols);
                dateFormat.setCalendar(new HebrewCalendar(Locale.forLanguageTag("ru")));
                return dateFormat.format(timestamp);

            case JEWISH_RU_GEN_PC:
                dateFormatSymbols = new DateFormatSymbols(Locale.forLanguageTag("ru"));
                dateFormatSymbols.setWeekdays(Tables.JEWISH_DOW_RU_PC_LONG);
                dateFormatSymbols.setShortWeekdays(Tables.JEWISH_DOW_RU_PC_SHORT);
                dateFormatSymbols.setMonths(Tables.JEWISH_MONTH_RU_GEN_PC_LONG);
                dateFormatSymbols.setShortMonths(Tables.JEWISH_MONTH_RU_PC_SHORT);
                dateFormat = new SimpleDateFormat(pattern, dateFormatSymbols);
                dateFormat.setCalendar(new HebrewCalendar(Locale.forLanguageTag("ru")));
                return dateFormat.format(timestamp);

            case JEWISH_RU_NOM_LC:
                dateFormatSymbols = new DateFormatSymbols(Locale.forLanguageTag("ru"));
                dateFormatSymbols.setWeekdays(Tables.JEWISH_DOW_RU_LC_LONG);
                dateFormatSymbols.setShortWeekdays(Tables.JEWISH_DOW_RU_LC_SHORT);
                dateFormatSymbols.setMonths(Tables.JEWISH_MONTH_RU_NOM_LC_LONG);
                dateFormatSymbols.setShortMonths(Tables.JEWISH_MONTH_RU_LC_SHORT);
                dateFormat = new SimpleDateFormat(pattern, dateFormatSymbols);
                dateFormat.setCalendar(new HebrewCalendar(Locale.forLanguageTag("ru")));
                return dateFormat.format(timestamp);

            case JEWISH_RU_GEN_LC:
                dateFormatSymbols = new DateFormatSymbols(Locale.forLanguageTag("ru"));
                dateFormatSymbols.setWeekdays(Tables.JEWISH_DOW_RU_LC_LONG);
                dateFormatSymbols.setShortWeekdays(Tables.JEWISH_DOW_RU_LC_SHORT);
                dateFormatSymbols.setMonths(Tables.JEWISH_MONTH_RU_GEN_LC_LONG);
                dateFormatSymbols.setShortMonths(Tables.JEWISH_MONTH_RU_LC_SHORT);
                dateFormat = new SimpleDateFormat(pattern, dateFormatSymbols);
                dateFormat.setCalendar(new HebrewCalendar(Locale.forLanguageTag("ru")));
                return dateFormat.format(timestamp);
        }
        throw new IllegalArgumentException("Unknown calendar type:" + calendarType);
    }

}