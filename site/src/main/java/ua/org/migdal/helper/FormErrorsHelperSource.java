package ua.org.migdal.helper;

import javax.inject.Inject;
import org.springframework.context.MessageSource;
import org.springframework.context.i18n.LocaleContextHolder;
import org.springframework.util.StringUtils;
import org.springframework.validation.Errors;
import org.springframework.validation.ObjectError;

import com.github.jknack.handlebars.Handlebars.SafeString;
import com.github.jknack.handlebars.Options;

import ua.org.migdal.helper.util.HelperUtils;

@HelperSource
public class FormErrorsHelperSource {

    @Inject
    private MessageSource messageSource;

    public CharSequence formErrors(Options options) {
        if (!(options.get("errors") instanceof Errors)) {
            return "";
        }

        String className = options.hash("class", "");
        String formName = options.hash("form");
        Errors errors = options.get("errors");

        if (formName != null && !errors.getObjectName().equals(formName)) {
            return "";
        }
        if (!errors.hasErrors()) {
            return "";
        }

        StringBuilder buf = new StringBuilder();
        for (ObjectError error : errors.getAllErrors()) {
            for (String code : error.getCodes()) {
                String message = messageSource.getMessage(code, null, "", LocaleContextHolder.getLocale());
                if (!StringUtils.isEmpty(message)) {
                    buf.append("<tr><td colspan=\"2\" class=\"error ");
                    HelperUtils.safeAppend(buf, className);
                    buf.append("\">");
                    HelperUtils.safeAppend(buf, message);
                    buf.append("</td></tr>");
                }
            }
        }
        return new SafeString(buf);
    }

}
