package ua.org.migdal.helper;

import java.awt.*;
import java.awt.image.BufferedImage;
import java.io.IOException;
import java.util.HashMap;
import java.util.Map;

import javax.imageio.ImageIO;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.context.ApplicationContext;
import org.springframework.web.util.HtmlUtils;

import com.github.jknack.handlebars.Handlebars;
import com.github.jknack.handlebars.Options;

@HelperSource
public class ImagesHelperSource {

    private static Logger log = LoggerFactory.getLogger(ImagesHelperSource.class);

    @Autowired
    private ApplicationContext applicationContext;

    private Map<String, Dimension> imageSizeCache = new HashMap<>();

    public CharSequence image(String href, Options options) {
        StringBuilder buf = new StringBuilder();
        buf.append("<img");
        appendAttribute(buf, "src", href);
        Dimension imageSize = getImageSize(href);
        if (imageSize != null) {
            appendAttribute(buf, "width", imageSize.width);
            appendAttribute(buf, "height", imageSize.height);
        }
        if (!options.<Boolean>get("englishDomain") || options.hash("altEn") == null) {
            appendHashParam(buf, "alt", options);
        } else {
            appendHashParam(buf, "altEn", "alt", options);
        }
        appendHashParam(buf, "title", options);
        appendHashParam(buf, "class", options);
        appendHashParam(buf, "id", options);
        appendHashParam(buf, "data_id", "data-id", options);
        appendHashParam(buf, "data_value", "data-value", options);
        appendHashParam(buf, "style", options);
        buf.append('>');
        return new Handlebars.SafeString(buf);
    }

    private void appendHashParam(StringBuilder buf, String name, Options options) {
        appendHashParam(buf, name, name, options);
    }

    private void appendHashParam(StringBuilder buf, String name, String attrName, Options options) {
        appendAttribute(buf, attrName, options.hash(name));
    }

    private void appendAttribute(StringBuilder buf, String attributeName, Object value) {
        if (value != null) {
            buf.append(' ');
            buf.append(attributeName);
            buf.append("=\"");
            buf.append(HtmlUtils.htmlEscape(value.toString()));
            buf.append('"');
        }
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
