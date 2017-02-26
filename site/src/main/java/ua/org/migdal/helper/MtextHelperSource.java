package ua.org.migdal.helper;

import com.github.jknack.handlebars.Handlebars;
import org.springframework.beans.factory.annotation.Autowired;
import ua.org.migdal.mtext.Mtext;
import ua.org.migdal.mtext.MtextService;

@HelperSource
public class MtextHelperSource {

    @Autowired
    private MtextService mtextService;

    public CharSequence mtext(Mtext mtext) {
        return new Handlebars.SafeString(mtextService.mtextToHtml(mtext, false, null).getBodyHtml());
    }

}
