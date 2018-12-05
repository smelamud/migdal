package ua.org.migdal.grp;

import java.util.List;

import org.springframework.expression.EvaluationContext;
import org.springframework.expression.Expression;
import org.springframework.expression.ExpressionParser;
import org.springframework.expression.ParserContext;
import org.springframework.util.StringUtils;

import ua.org.migdal.manager.IdentManager;
import ua.org.migdal.util.TrackUtils;

public abstract class SubtreeValue {

    private String topic = "";
    private String subtree = "";
    private String value = "";

    private String subtreeTrack;
    private Expression valueExpression;

    public String getTopic() {
        return topic;
    }

    public void setTopic(String topic) {
        this.topic = topic;
    }

    public String getSubtree() {
        return subtree;
    }

    public void setSubtree(String subtree) {
        this.subtree = subtree;
    }

    private boolean isDefault() {
        return StringUtils.isEmpty(subtree) && StringUtils.isEmpty(topic);
    }

    private boolean isRecursive() {
        return !StringUtils.isEmpty(subtree);
    }

    private boolean isUnder(String track) {
        if (isDefault()) {
            return true;
        }
        if (!isRecursive()) {
            return TrackUtils.parent(track).equals(subtreeTrack);
        } else {
            return track.contains(subtreeTrack);
        }
    }

    protected String getValue() {
        return value;
    }

    protected void setValue(String value) {
        this.value = value;
    }

    protected String getValue(EvaluationContext evaluationContext) {
        return valueExpression.getValue(evaluationContext, String.class);
    }

    void parseExpressions(IdentManager identManager, ExpressionParser parser, ParserContext parserContext) {
        if (!isDefault()) {
            if (!isRecursive()) {
                subtreeTrack = TrackUtils.track(identManager.idOrIdent(topic));
            } else {
                subtreeTrack = TrackUtils.track(identManager.idOrIdent(subtree));
            }
        }
        valueExpression = parser.parseExpression(value, parserContext);
    }

    static String evaluateSubtreeExpression(List<? extends SubtreeValue> subtreeValues,
                                            Expression trackExpression,
                                            EvaluationContext evaluationContext) {
        String track = trackExpression.getValue(evaluationContext, String.class);
        for (SubtreeValue subtreeValue : subtreeValues) {
            if (subtreeValue.isUnder(track)) {
                return subtreeValue.getValue(evaluationContext);
            }
        }
        return "";
    }

}