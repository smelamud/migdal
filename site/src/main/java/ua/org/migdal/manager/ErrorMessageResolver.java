package ua.org.migdal.manager;

import javax.inject.Inject;

import org.springframework.context.MessageSource;
import org.springframework.context.expression.MapAccessor;
import org.springframework.expression.EvaluationContext;
import org.springframework.expression.ExpressionParser;
import org.springframework.expression.ParserContext;
import org.springframework.expression.spel.standard.SpelExpressionParser;
import org.springframework.expression.spel.support.StandardEvaluationContext;
import org.springframework.stereotype.Service;
import ua.org.migdal.grp.TemplateParserContext;

@Service
public class ErrorMessageResolver {

    @Inject
    private MessageSource messageSource;

    private ExpressionParser parser = new SpelExpressionParser();

    public ErrorMessageContext createContext() {
        return createContext(null);
    }

    public ErrorMessageContext createContext(Object object) {
        if (object != null) {
            ParserContext parserContext = new TemplateParserContext();
            EvaluationContext evaluationContext = new StandardEvaluationContext(object);
            evaluationContext.getPropertyAccessors().add(new MapAccessor());
            return new ErrorMessageContext(messageSource, parser, parserContext, evaluationContext);
        } else {
            return new ErrorMessageContext(messageSource, parser);
        }
    }

}
