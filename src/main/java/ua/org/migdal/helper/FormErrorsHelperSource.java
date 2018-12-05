package ua.org.migdal.helper;

import java.util.List;

import javax.inject.Inject;

import org.springframework.validation.Errors;

import com.github.jknack.handlebars.Handlebars.SafeString;
import com.github.jknack.handlebars.Options;

import ua.org.migdal.helper.util.HelperUtils;
import ua.org.migdal.manager.ErrorMessageResolver;

@HelperSource
public class FormErrorsHelperSource {

    @Inject
    private ErrorMessageResolver errorMessageResolver;

    public CharSequence formErrors(Options options) {
        if (!(options.get("errors") instanceof Errors)) {
            return "";
        }

        String className = options.hash("class", "");
        String formName = options.hash("form");
        String exceptFormName = options.hash("exceptForm");
        Object object = options.hash("object");
        Errors errors = options.get("errors");

        if (formName != null && !errors.getObjectName().equals(formName)) {
            return "";
        }
        if (exceptFormName != null && errors.getObjectName().equals(exceptFormName)) {
            return "";
        }
        List<String> messages = errorMessageResolver.createContext(object).getMessages(errors);
        StringBuilder buf = new StringBuilder();
        for (String message : messages) {
            buf.append("<tr><td colspan=\"2\" class=\"error ");
            HelperUtils.safeAppend(buf, className);
            buf.append("\">");
            HelperUtils.safeAppend(buf, message);
            buf.append("</td></tr>");
        }
        return new SafeString(buf);
    }

}
