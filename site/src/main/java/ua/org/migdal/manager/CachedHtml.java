package ua.org.migdal.manager;

import java.time.temporal.TemporalAmount;

import org.springframework.ui.Model;
import ua.org.migdal.util.Utils;

public class CachedHtml {

    private HtmlCacheManager htmlCacheManager;

    private StringBuilder ident;
    private TemporalAmount period;
    private boolean dependsOnPostings;
    private boolean dependsOnForums;
    private boolean dependsOnTopics;

    CachedHtml(HtmlCacheManager htmlCacheManager, String objectName) {
        this.htmlCacheManager = htmlCacheManager;
        ident = new StringBuilder(objectName);
    }

    public boolean isValid() {
        return htmlCacheManager.isValid(this);
    }

    public boolean isInvalid() {
        return !isValid();
    }

    public CachedHtml of(String parameter) {
        ident.append('-');
        ident.append(parameter);
        return this;
    }

    public CachedHtml of(long parameter) {
        ident.append('-');
        ident.append(parameter);
        return this;
    }

    public CachedHtml of(boolean parameter) {
        ident.append('-');
        ident.append(parameter);
        return this;
    }

    public CachedHtml ofTopicsIndex(Model model) {
        return of((String) model.asMap().get("topicsIndex"));
    }

    public CachedHtml ofRandom(int n) {
        return of(Utils.random(0, n));
    }

    public CachedHtml during(TemporalAmount period) {
        this.period = period;
        return this;
    }

    public CachedHtml onPostings() {
        dependsOnPostings = true;
        return this;
    }

    public CachedHtml onForums() {
        dependsOnForums = true;
        return this;
    }

    public CachedHtml onTopics() {
        dependsOnTopics = true;
        return this;
    }

    String getIdent() {
        return ident.toString();
    }

    TemporalAmount getPeriod() {
        return period;
    }

    boolean isDependsOnPostings() {
        return dependsOnPostings;
    }

    boolean isDependsOnForums() {
        return dependsOnForums;
    }

    boolean isDependsOnTopics() {
        return dependsOnTopics;
    }

}
