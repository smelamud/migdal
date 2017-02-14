package ua.org.migdal.helper;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.context.MessageSource;
import org.springframework.context.i18n.LocaleContextHolder;
import org.springframework.util.StringUtils;
import org.springframework.validation.Errors;
import org.springframework.validation.ObjectError;
import org.springframework.web.util.HtmlUtils;

import com.github.jknack.handlebars.Handlebars;
import com.github.jknack.handlebars.Options;

@HelperSource
public class FormErrorsHelperSource {

    @Autowired
    private MessageSource messageSource;

    public CharSequence formErrors(Options options) {
        if (!(options.get("errors") instanceof Errors)) {
            return "";
        }

        String className = options.hash("class", "");
        Errors errors = (Errors) options.get("errors");

        if (!errors.hasErrors()) {
            return "";
        }

        StringBuilder buf = new StringBuilder();
        for (ObjectError error : errors.getAllErrors()) {
            for (String code : error.getCodes()) {
                String message = messageSource.getMessage(code, null, "", LocaleContextHolder.getLocale());
                if (!StringUtils.isEmpty(message)) {
                    buf.append("<tr><td colspan=\"2\" class=\"error ");
                    buf.append(className);
                    buf.append("\">");
                    buf.append(HtmlUtils.htmlEscape(message));
                    buf.append("</td></tr>");
                }
            }
        }
        return new Handlebars.SafeString(buf);
    }

}
