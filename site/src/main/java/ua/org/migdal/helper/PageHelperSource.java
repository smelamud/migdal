package ua.org.migdal.helper;

import com.github.jknack.handlebars.Handlebars.SafeString;
import com.github.jknack.handlebars.Options;
import org.springframework.web.util.HtmlUtils;

@HelperSource
public class PageHelperSource {

    public CharSequence subtitle(Options options) {
        String pageTitle = HtmlUtils.htmlEscape(options.get("pageTitle"));
        if (pageTitle != null && !pageTitle.isEmpty() && !pageTitle.equals("Главная") && !pageTitle.equals("Home")) {
            return new SafeString(String.format("<div class=\"page-title\">%s</div>", pageTitle));
        } else {
            return "";
        }
    }

}