package ua.org.migdal.helper;

import java.awt.Dimension;
import java.awt.image.BufferedImage;
import java.io.IOException;
import java.util.HashMap;
import java.util.Map;

import javax.imageio.ImageIO;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import javax.inject.Inject;
import org.springframework.context.ApplicationContext;

import com.github.jknack.handlebars.Handlebars.SafeString;
import com.github.jknack.handlebars.Options;
import org.springframework.util.StringUtils;
import ua.org.migdal.helper.util.HelperUtils;
import ua.org.migdal.session.RequestContext;

@HelperSource
public class ImagesHelperSource {

    private static Logger log = LoggerFactory.getLogger(ImagesHelperSource.class);

    @Inject
    private ApplicationContext applicationContext;

    @Inject
    private RequestContext requestContext;

    private Map<String, Dimension> imageSizeCache = new HashMap<>();

    public CharSequence image(CharSequence href, Options options) {
        CharSequence alt = options.hash("altEn");
        if (!requestContext.isEnglish() || alt == null) {
            alt = options.hash("alt");
        }
        CharSequence title = options.hash("title");
        CharSequence klass = options.hash("class");
        CharSequence id = options.hash("id");
        CharSequence dataId = options.hash("dataId");
        CharSequence dataValue = options.hash("dataValue");
        CharSequence style = options.hash("style");

        StringBuilder buf = new StringBuilder();
        boolean button = HelperUtils.boolArg(options.hash("button", false));
        buf.append(!button ? "<img" : "<input type=\"image\"");
        HelperUtils.appendAttr(buf, "src", href);
        Dimension imageSize = getImageSize(href.toString());
        if (imageSize != null) {
            HelperUtils.appendAttr(buf, "width", imageSize.width);
            HelperUtils.appendAttr(buf, "height", imageSize.height);
        }
        HelperUtils.appendAttr(buf, "alt", alt);
        HelperUtils.appendAttr(buf, "title", title);
        HelperUtils.appendAttr(buf, "class", klass);
        HelperUtils.appendAttr(buf, "id", id);
        HelperUtils.appendAttr(buf, "data-id", dataId);
        HelperUtils.appendAttr(buf, "data-value", dataValue);
        HelperUtils.appendAttr(buf, "style", style);
        buf.append('>');
        return new SafeString(buf);
    }

    CharSequence image(CharSequence href) {
        return image(href, null, null);
    }

    CharSequence image(CharSequence href, CharSequence alt, CharSequence title) {
        StringBuilder buf = new StringBuilder();
        buf.append("<img");
        HelperUtils.appendAttr(buf, "src", href);
        Dimension imageSize = getImageSize(href.toString());
        if (imageSize != null) {
            HelperUtils.appendAttr(buf, "width", imageSize.width);
            HelperUtils.appendAttr(buf, "height", imageSize.height);
        }
        HelperUtils.appendAttr(buf, "alt", alt);
        HelperUtils.appendAttr(buf, "title", title);
        buf.append('>');
        return new SafeString(buf);
    }

    private Dimension getImageSize(String path) {
        Dimension imageSize = imageSizeCache.get(path);
        if (imageSize != null) {
            return imageSize;
        }

        try {
            BufferedImage image =
                    ImageIO.read(applicationContext.getResource("classpath:static" + path).getInputStream());
            imageSize = new Dimension(image.getWidth(), image.getHeight());
            imageSizeCache.put(path, imageSize);
            return imageSize;
        } catch (IOException e) {
            log.error("Error determining size of the image '{}': {}", path, e.getMessage());
            return null;
        }
    }

}
