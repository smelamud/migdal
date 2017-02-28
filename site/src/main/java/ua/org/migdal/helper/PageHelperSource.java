package ua.org.migdal.helper;

import com.github.jknack.handlebars.Handlebars.SafeString;
import com.github.jknack.handlebars.Options;

@HelperSource
public class PageHelperSource {

    public CharSequence subtitle(Options options) {
        String pageTitle = options.get("pageTitle");
        if (pageTitle != null && !pageTitle.isEmpty() && !pageTitle.equals("Главная") && !pageTitle.equals("Home")) {
            return new SafeString(String.format("<div class=\"page-title\">%s</div>", HelperUtils.he(pageTitle)));
        } else {
            return "";
        }
    }

}