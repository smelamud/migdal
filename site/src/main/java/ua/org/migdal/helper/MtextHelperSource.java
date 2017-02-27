package ua.org.migdal.helper;

import java.util.List;

import com.github.jknack.handlebars.Handlebars;
import com.github.jknack.handlebars.Options;

import org.springframework.beans.factory.annotation.Autowired;
import ua.org.migdal.Config;
import ua.org.migdal.data.InnerImage;
import ua.org.migdal.mtext.InnerImageBlock;
import ua.org.migdal.mtext.Mtext;
import ua.org.migdal.mtext.MtextConverter;
import ua.org.migdal.mtext.MtextToHtml;
import ua.org.migdal.mtext.exception.MtextConverterException;

@HelperSource
public class MtextHelperSource {

    @Autowired
    private Config config;

    public CharSequence mtext(Mtext mtext, Options options) {
        List<InnerImage> innerImages = options.hash("innerImages");

        MtextToHtml handler = new MtextToHtml(mtext.getFormat(), mtext.isIgnoreWrongFormat(), mtext.getId(),
                                              InnerImageBlock.getImageBlocks(innerImages));
        handler.setConfig(config);
        try {
            MtextConverter.convert(mtext, handler);
            return new Handlebars.SafeString(handler.getHtmlBody());
        } catch (MtextConverterException e) {
            return new Handlebars.SafeString(
                    String.format("<b>** %s: %s</b>", e.getMessage(), e.getCause().getMessage()));
        }
    }

}
