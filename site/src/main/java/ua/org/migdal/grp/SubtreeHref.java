package ua.org.migdal.grp;

import org.springframework.expression.EvaluationContext;
import org.springframework.expression.Expression;
import org.springframework.expression.ExpressionParser;
import org.springframework.expression.ParserContext;
import org.springframework.util.StringUtils;
import ua.org.migdal.manager.IdentManager;
import ua.org.migdal.util.TrackUtils;

public class SubtreeHref {

    private String subtree = "";
    private String href = "";

    private String subtreeTrack;
    private Expression hrefExpression;

    public SubtreeHref() {
    }

    public String getSubtree() {
        return subtree;
    }

    public void setSubtree(String subtree) {
        this.subtree = subtree;
    }

    public boolean isDefault() {
        return StringUtils.isEmpty(subtree);
    }

    public boolean isUnder(String track) {
        return isDefault() || track.contains(subtreeTrack);
    }

    public String getHref() {
        return href;
    }

    public void setHref(String href) {
        this.href = href;
    }

    public String getHref(EvaluationContext evaluationContext) {
        return hrefExpression.getValue(evaluationContext, String.class);
    }

    void parseExpressions(IdentManager identManager, ExpressionParser parser, ParserContext parserContext) {
        if (!isDefault()) {
            subtreeTrack = TrackUtils.track(identManager.idOrIdent(subtree));
        }
        hrefExpression = parser.parseExpression(href, parserContext);
    }

}