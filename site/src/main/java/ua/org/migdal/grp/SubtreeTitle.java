package ua.org.migdal.grp;

import org.springframework.expression.EvaluationContext;

public class SubtreeTitle extends SubtreeValue {

    public SubtreeTitle() {
    }

    public String getTitle() {
        return getValue();
    }

    public void setTitle(String title) {
        setValue(title);
    }

    public String getTitle(EvaluationContext evaluationContext) {
        return getValue(evaluationContext);
    }

}