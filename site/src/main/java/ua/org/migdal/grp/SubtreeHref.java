package ua.org.migdal.grp;

import org.springframework.expression.EvaluationContext;

public class SubtreeHref extends SubtreeValue {

    public SubtreeHref() {
    }

    public String getHref() {
        return getValue();
    }

    public void setHref(String href) {
        setValue(href);
    }

    public String getHref(EvaluationContext evaluationContext) {
        return getValue(evaluationContext);
    }

}