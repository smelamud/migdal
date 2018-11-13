package ua.org.migdal.helper;

import java.io.IOException;
import javax.inject.Inject;

import com.github.jknack.handlebars.Handlebars.SafeString;
import com.github.jknack.handlebars.Options;
import ua.org.migdal.helper.exception.MissingArgumentException;
import ua.org.migdal.manager.CachedHtml;
import ua.org.migdal.manager.HtmlCacheManager;

@HelperSource
public class HtmlCacheHelperSource {

    @Inject
    private HtmlCacheManager htmlCacheManager;

    public CharSequence cached(CachedHtml cachedHtml, Options options) throws IOException {
        if (cachedHtml == null) {
            throw new MissingArgumentException("cachedHtml");
        }
        CharSequence content = htmlCacheManager.get(cachedHtml);
        if (content != null) {
            return new SafeString(content);
        }
        content = options.apply(options.fn);
        htmlCacheManager.store(cachedHtml, content);
        return content;
    }

}
