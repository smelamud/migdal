package ua.org.migdal.mtext;

import java.util.List;

import javax.annotation.PostConstruct;
import javax.inject.Inject;

import org.springframework.stereotype.Service;

import com.github.jknack.handlebars.Handlebars.SafeString;

import ua.org.migdal.Config;
import ua.org.migdal.data.InnerImage;
import ua.org.migdal.helper.UsersHelperSource;
import ua.org.migdal.util.XmlConverter;
import ua.org.migdal.util.XmlUtils;
import ua.org.migdal.util.exception.XmlConverterException;

@Service
public class MtextConverter {

    private static MtextConverter instance;

    @Inject
    private Config config;

    @Inject
    private UsersHelperSource usersHelperSource;

    @PostConstruct
    public void init() {
        instance = this;
    }

    public static MtextConverter getInstance() {
        return instance;
    }

    public CharSequence toHtml(Mtext mtext) {
        return toHtml(mtext, null);
    }

    public CharSequence toHtml(Mtext mtext, List<InnerImage> innerImages) {
        if (mtext == null) {
            return new SafeString("<i>null</i>");
        }
        if (!XmlUtils.hasMarkup(mtext.getXml())) {
            return new SafeString(mtext.getXml());
        }

        MtextToHtml handler = new MtextToHtml(mtext.getFormat(), mtext.isIgnoreWrongFormat(), mtext.getId(),
                InnerImageBlock.getImageBlocks(innerImages));
        handler.setConfig(config);
        handler.setUsersHelperSource(usersHelperSource);
        try {
            XmlConverter.convert(mtext.getXml(), handler);
            return new SafeString(handler.getHtmlBody());
        } catch (XmlConverterException e) {
            return new SafeString(String.format("<b>** %s: %s</b>", e.getMessage(), e.getCause().getMessage()));
        }
    }

}