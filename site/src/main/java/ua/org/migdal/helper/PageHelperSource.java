package ua.org.migdal.helper;

import java.time.LocalDateTime;

import javax.inject.Inject;

import com.github.jknack.handlebars.Handlebars.SafeString;
import com.github.jknack.handlebars.Options;
import com.ibm.icu.util.Calendar;
import com.ibm.icu.util.GregorianCalendar;
import com.ibm.icu.util.HebrewCalendar;

import ua.org.migdal.helper.util.HelperUtils;
import ua.org.migdal.util.Utils;

@HelperSource
public class PageHelperSource {

    @Inject
    private ImagesHelperSource imagesHelperSource;

    public CharSequence subtitle(Options options) {
        String title = options.hash("title");
        String pageTitle = title != null ? title : options.get("pageTitle");

        if (pageTitle != null && !pageTitle.isEmpty() && !pageTitle.equals("Главная") && !pageTitle.equals("Home")) {
            return new SafeString(String.format("<div class=\"page-title\">%s</div>", HelperUtils.he(pageTitle)));
        } else {
            return "";
        }
    }

    public CharSequence tzdaka(Options options) {
        StringBuilder buf = new StringBuilder();
        buf.append("<a href=\"/migdal/tzdaka/\">");
        String title = "Наши реквизиты для цдаки";
        buf.append(imagesHelperSource.image("/pics/tzedaka.jpg", title, title));
        buf.append("</a>");
        return new SafeString(buf);
    }

    public CharSequence hannukah(Options options) {
        GregorianCalendar gToday = new GregorianCalendar();
        Utils.copyToCalendar(LocalDateTime.now(), gToday);
        HebrewCalendar today = Utils.toHebrew(gToday);
        if (LocalDateTime.now().getHour() >= 5) {
            today.add(Calendar.DAY_OF_MONTH, 1);
        }
        HebrewCalendar holiday = new HebrewCalendar(today.get(Calendar.YEAR), HebrewCalendar.KISLEV, 25);
        if (today.before(holiday)) {
            return "";
        }
        for (int i = 1; i <= 8; i++) {
            if (isSameDay(today, holiday)) {
                return imagesHelperSource.image(String.format("pics/hk-%d.gif", i), null, null, null, null, null, null,
                                                "hannukah");
            }
            holiday.add(Calendar.DAY_OF_MONTH, 1);
        }
        return "";
    }

    private boolean isSameDay(HebrewCalendar first, HebrewCalendar second) {
        return first.get(Calendar.DAY_OF_MONTH) == second.get(Calendar.DAY_OF_MONTH)
                && first.get(Calendar.MONTH) == second.get(Calendar.MONTH);
    }

}