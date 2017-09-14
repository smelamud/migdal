package ua.org.migdal.helper;

import javax.inject.Inject;
import org.springframework.context.MessageSource;
import org.springframework.context.expression.MapAccessor;
import org.springframework.context.i18n.LocaleContextHolder;
import org.springframework.expression.EvaluationContext;
import org.springframework.expression.ExpressionParser;
import org.springframework.expression.ParserContext;
import org.springframework.expression.spel.standard.SpelExpressionParser;
import org.springframework.expression.spel.support.StandardEvaluationContext;
import org.springframework.util.StringUtils;
import org.springframework.validation.Errors;
import org.springframework.validation.ObjectError;

import com.github.jknack.handlebars.Handlebars.SafeString;
import com.github.jknack.handlebars.Options;

import ua.org.migdal.grp.TemplateParserContext;
import ua.org.migdal.helper.util.HelperUtils;

@HelperSource
public class FormErrorsHelperSource {

    @Inject
    private MessageSource messageSource;

    private ExpressionParser parser = new SpelExpressionParser();

    public CharSequence formErrors(Options options) {
        if (!(options.get("errors") instanceof Errors)) {
            return "";
        }

        String className = options.hash("class", "");
        String formName = options.hash("form");
        Object object = options.hash("object");
        Errors errors = options.get("errors");

        if (formName != null && !errors.getObjectName().equals(formName)) {
            return "";
        }
        if (!errors.hasErrors()) {
            return "";
        }

        ParserContext parserContext = null;
        EvaluationContext evaluationContext = null;
        if (object != null) {
            parserContext = new TemplateParserContext();
            evaluationContext = new StandardEvaluationContext(object);
            evaluationContext.getPropertyAccessors().add(new MapAccessor());
        }

        StringBuilder buf = new StringBuilder();
        for (ObjectError error : errors.getAllErrors()) {
            for (String code : error.getCodes()) {
                String message = messageSource.getMessage(code, null, "", LocaleContextHolder.getLocale());
                if (object != null) {
                    message = parser.parseExpression(message, parserContext).getValue(evaluationContext, String.class);
                }
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
