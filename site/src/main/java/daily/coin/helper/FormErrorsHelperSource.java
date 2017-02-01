package daily.coin.helper;

import java.util.Map;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.context.MessageSource;
import org.springframework.context.i18n.LocaleContextHolder;
import org.springframework.util.StringUtils;
import org.springframework.validation.Errors;
import org.springframework.validation.ObjectError;

import com.github.jknack.handlebars.Handlebars;
import com.github.jknack.handlebars.Options;

@HelperSource
public class FormErrorsHelperSource {

    @Autowired
    private MessageSource messageSource;

    public CharSequence formErrors(Map<String, Object> model, Options options) {
        if (!(model.get("errors") instanceof Errors)) {
            return "";
        }

        String className = options.hash("class", "");

        StringBuilder buf = new StringBuilder();
        Errors errors = (Errors) model.get("errors");
        if (errors.hasErrors()) {
            for (ObjectError error : errors.getAllErrors()) {
                buf.append("<div class=\"row\"><div class=\"error ");
                buf.append(className);
                buf.append("\">");
                for (String code : error.getCodes()) {
                    String message = messageSource.getMessage(code, null, "", LocaleContextHolder.getLocale());
                    if (!StringUtils.isEmpty(message)) {
                        buf.append(message);
                        break;
                    }
                }
                buf.append("</div></div>");
            }
        }
        return new Handlebars.SafeString(buf);
    }

    public CharSequence hasError(Map<String, Object> model, String fieldName) {
        if (!(model.get("errors") instanceof Errors)) {
            return "";
        }

        Errors errors = (Errors) model.get("errors");
        return errors.hasFieldErrors(fieldName) ? "has-error" : "";
    }

}
