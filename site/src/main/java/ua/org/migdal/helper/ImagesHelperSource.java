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
        StringBuilder buf = new StringBuilder();
        boolean button = HelperUtils.boolArg(options.hash("button", false));
        buf.append(!button ? "<img" : "<input type=\"image\"");
        HelperUtils.appendAttr(buf, "src", href);
        Dimension imageSize = getImageSize(href.toString());
        if (imageSize != null) {
            HelperUtils.appendAttr(buf, "width", imageSize.width);
            HelperUtils.appendAttr(buf, "height", imageSize.height);
        }
        if (!requestContext.isEnglish() || options.hash("altEn") == null) {
            HelperUtils.appendOptionalArgAttr(buf, "alt", options);
        } else {
            HelperUtils.appendOptionalArgAttr(buf, "altEn", "alt", options);
        }
        HelperUtils.appendOptionalArgAttr(buf, "title", options);
        HelperUtils.appendOptionalArgAttr(buf, "class", options);
        HelperUtils.appendOptionalArgAttr(buf, "id", options);
        HelperUtils.appendOptionalArgAttr(buf, "data_id", "data-id", options);
        HelperUtils.appendOptionalArgAttr(buf, "data_value", "data-value", options);
        HelperUtils.appendOptionalArgAttr(buf, "style", options);
        buf.append('>');
        return new SafeString(buf);
    }

    CharSequence image(CharSequence href) {
        StringBuilder buf = new StringBuilder();
        buf.append("<img");
        HelperUtils.appendAttr(buf, "src", href);
        Dimension imageSize = getImageSize(href.toString());
        if (imageSize != null) {
            HelperUtils.appendAttr(buf, "width", imageSize.width);
            HelperUtils.appendAttr(buf, "height", imageSize.height);
        }
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
