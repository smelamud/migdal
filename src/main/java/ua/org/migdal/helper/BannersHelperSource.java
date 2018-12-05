package ua.org.migdal.helper;

import com.github.jknack.handlebars.Handlebars.SafeString;
import com.github.jknack.handlebars.Options;

import ua.org.migdal.helper.util.HelperUtils;
import ua.org.migdal.util.Utils;

@HelperSource
public class BannersHelperSource {
    
    private static class Banner {
    
        private String targetUrl;
        private String imageUrl;
        private String title;

        Banner(String targetUrl, String imageUrl, String title) {
            this.targetUrl = targetUrl;
            this.imageUrl = imageUrl;
            this.title = title;
        }
    }

    private static final Banner[] BANNERS = new Banner[] {
            new Banner("http://www.odessitclub.org/", "/pics/banners/ioc.gif", "Всемирный клуб одесситов"),
            new Banner("http://www.lookstein.org/russian/", "/pics/banners/lookstein.gif", "Еврейский педсовет"),
            new Banner("http://www.antho.net/", "https://www.antho.net/banners/88/88x31-03.gif", "Jerusalem Anthologia"),
            new Banner("http://www.jewniverse.ru/", "/pics/banners/jewniverse.jpg", "Jewniverse - Yiddish Shtetl"),
            new Banner("http://velelens.livejournal.com/", "/pics/banners/secret.jpg", "Еженедельник \"Секрет\""),
            new Banner("http://www.drnona.info/", "/pics/banners/drnona.gif", "Dr. NONA")
    };

    public CharSequence banners(Options options) {
        int[] n = Utils.randomArray(3, 0, BANNERS.length);
        StringBuilder buf = new StringBuilder();
        for (int m : n) {
            buf.append("<a");
            HelperUtils.appendAttr(buf, "href", BANNERS[m].targetUrl);
            buf.append(" target=\"_blank\">");
            buf.append("<img");
            HelperUtils.appendAttr(buf, "src", BANNERS[m].imageUrl);
            buf.append(" width=\"88\" height=\"31\"");
            HelperUtils.appendAttr(buf, "alt", BANNERS[m].title);
            buf.append('>');
            buf.append("</a> ");
        }
        return new SafeString(buf);
    }

}
