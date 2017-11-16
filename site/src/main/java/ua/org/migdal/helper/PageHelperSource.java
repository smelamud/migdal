package ua.org.migdal.helper;

import javax.inject.Inject;

import com.github.jknack.handlebars.Handlebars.SafeString;
import com.github.jknack.handlebars.Options;
import ua.org.migdal.helper.util.HelperUtils;

@HelperSource
public class PageHelperSource {

    @Inject
    private ImagesHelperSource imagesHelperSource;

    public CharSequence subtitle(Options options) {
        String pageTitle = options.get("pageTitle");
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

}