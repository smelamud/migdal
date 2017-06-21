package ua.org.migdal.helper;

import java.util.List;

import javax.inject.Inject;

import com.github.jknack.handlebars.Handlebars.SafeString;
import com.github.jknack.handlebars.Options;

import ua.org.migdal.Config;
import ua.org.migdal.data.InnerImage;
import ua.org.migdal.mtext.InnerImageBlock;
import ua.org.migdal.mtext.Mtext;
import ua.org.migdal.mtext.MtextToHtml;
import ua.org.migdal.util.XmlConverter;
import ua.org.migdal.util.exception.XmlConverterException;

@HelperSource
public class MtextHelperSource {

    @Inject
    private Config config;

    public CharSequence mtext(Mtext mtext, Options options) {
        List<InnerImage> innerImages = options.hash("innerImages");

        MtextToHtml handler = new MtextToHtml(mtext.getFormat(), mtext.isIgnoreWrongFormat(), mtext.getId(),
                                              InnerImageBlock.getImageBlocks(innerImages));
        handler.setConfig(config);
        try {
            XmlConverter.convert(mtext.getXml(), handler);
            return new SafeString(handler.getHtmlBody());
        } catch (XmlConverterException e) {
            return new SafeString(String.format("<b>** %s: %s</b>", e.getMessage(), e.getCause().getMessage()));
        }
    }

}
