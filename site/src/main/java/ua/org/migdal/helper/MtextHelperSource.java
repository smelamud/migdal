package ua.org.migdal.helper;

import javax.inject.Inject;

import com.github.jknack.handlebars.Options;

import ua.org.migdal.mtext.Mtext;
import ua.org.migdal.mtext.MtextConverter;

@HelperSource
public class MtextHelperSource {

    @Inject
    private MtextConverter mtextConverter;

    public CharSequence mtext(Mtext mtext, Options options) {
        return mtextConverter.toHtml(mtext, options.hash("innerImages"));
    }

}
