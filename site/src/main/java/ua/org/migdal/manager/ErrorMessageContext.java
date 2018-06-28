package ua.org.migdal.manager;

import java.util.ArrayList;
import java.util.Collections;
import java.util.List;

import javax.validation.constraints.NotNull;

import org.springframework.context.MessageSource;
import org.springframework.context.i18n.LocaleContextHolder;
import org.springframework.expression.EvaluationContext;
import org.springframework.expression.ExpressionParser;
import org.springframework.expression.ParserContext;
import org.springframework.util.StringUtils;
import org.springframework.validation.Errors;
import org.springframework.validation.ObjectError;

public class ErrorMessageContext {

    private MessageSource messageSource;
    private ExpressionParser parser;
    private ParserContext parserContext;
    private EvaluationContext evaluationContext;

    public ErrorMessageContext(MessageSource messageSource, ExpressionParser parser) {
        this(messageSource, parser, null, null);
    }

    public ErrorMessageContext(
            MessageSource messageSource,
            ExpressionParser parser,
            ParserContext parserContext,
            EvaluationContext evaluationContext) {

        this.messageSource = messageSource;
        this.parser = parser;
        this.parserContext = parserContext;
        this.evaluationContext = evaluationContext;
    }

    public String getMessage(String code) {
        String message = messageSource.getMessage(code, null, "", LocaleContextHolder.getLocale());
        if (parserContext != null) {
            message = parser.parseExpression(message, parserContext).getValue(evaluationContext, String.class);
        }
        return message;
    }

    public @NotNull List<String> getMessages(Errors errors) {
        if (!errors.hasErrors()) {
            return Collections.emptyList();
        }

        List<String> messages = new ArrayList<>();
        for (ObjectError error : errors.getAllErrors()) {
            String[] codes = error.getCodes();
            if (codes == null) {
                continue;
            }
            for (String code : codes) {
                String message = getMessage(code);
                if (!StringUtils.isEmpty(message)) {
                    messages.add(message);
                }
            }
        }
        return messages;
    }

}
