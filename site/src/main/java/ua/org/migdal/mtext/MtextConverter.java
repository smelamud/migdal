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

    public MtextConverted convert(Mtext mtext, List<InnerImage> innerImages) {
        if (mtext == null) {
            return new SimpleMtextConverted("<i>null</i>");
        }
        if (!XmlUtils.hasMarkup(mtext.getXml())) {
            return new SimpleMtextConverted(mtext.getXml());
        }

        MtextToHtml handler = new MtextToHtml(mtext.getFormat(), mtext.isIgnoreWrongFormat(), mtext.getId(),
                InnerImageBlock.getImageBlocks(innerImages));
        handler.setConfig(config);
        handler.setUsersHelperSource(usersHelperSource);
        try {
            XmlConverter.convert(mtext.getXml(), handler);
            return handler;
        } catch (XmlConverterException e) {
            return new SimpleMtextConverted(String.format("<b>** %s: %s</b>", e.getMessage(), e.getCause().getMessage()));
        }
    }

    public CharSequence toHtml(Mtext mtext) {
        return toHtml(mtext, null);
    }

    public CharSequence toHtml(Mtext mtext, List<InnerImage> innerImages) {
        return new SafeString(convert(mtext, innerImages).getHtmlBody());
    }

    private static class SimpleMtextConverted implements MtextConverted {

        private String html;

        SimpleMtextConverted(String html) {
            this.html = html;
        }

        @Override
        public CharSequence getHtmlBody() {
            return html;
        }

        @Override
        public CharSequence getHtmlFootnotes() {
            return "";
        }

    }

}