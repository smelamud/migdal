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
        HelperUtils.appendAttribute(buf, "src", href);
        Dimension imageSize = getImageSize(href);
        if (imageSize != null) {
            HelperUtils.appendAttribute(buf, "width", imageSize.width);
            HelperUtils.appendAttribute(buf, "height", imageSize.height);
        }
        if (!options.get("userDomain").equals("english") || options.hash("altEn") == null) {
            HelperUtils.appendHashParam(buf, "alt", options);
        } else {
            HelperUtils.appendHashParam(buf, "altEn", "alt", options);
        }
        HelperUtils.appendHashParam(buf, "title", options);
        HelperUtils.appendHashParam(buf, "class", options);
        HelperUtils.appendHashParam(buf, "id", options);
        HelperUtils.appendHashParam(buf, "data_id", "data-id", options);
        HelperUtils.appendHashParam(buf, "data_value", "data-value", options);
        HelperUtils.appendHashParam(buf, "style", options);
        buf.append('>');
        return new Handlebars.SafeString(buf);
    }

    CharSequence image(String href) {
        StringBuilder buf = new StringBuilder();
        buf.append("<img");
        HelperUtils.appendAttribute(buf, "src", href);
        Dimension imageSize = getImageSize(href);
        if (imageSize != null) {
            HelperUtils.appendAttribute(buf, "width", imageSize.width);
            HelperUtils.appendAttribute(buf, "height", imageSize.height);
        }
        buf.append('>');
        return new Handlebars.SafeString(buf);
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
