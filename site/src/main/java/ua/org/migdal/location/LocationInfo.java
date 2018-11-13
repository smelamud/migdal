package ua.org.migdal.location;

import java.lang.annotation.ElementType;
import java.lang.annotation.Retention;
import java.lang.annotation.RetentionPolicy;
import java.lang.annotation.Target;
import java.lang.reflect.Field;
import java.util.ArrayDeque;
import java.util.Deque;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.ui.Model;
import org.springframework.util.StringUtils;
import ua.org.migdal.controller.TopicsService;
import ua.org.migdal.data.Posting;

public class LocationInfo {

    @Target(ElementType.FIELD)
    @Retention(RetentionPolicy.RUNTIME)
    private @interface NonPublished {
    }

    private static final Logger log = LoggerFactory.getLogger(LocationInfo.class);

    private static final String UNTITLED = "Untitled";

    @NonPublished
    private Model model;
    @NonPublished
    private LocationInfo parent;
    @NonPublished
    private String uri;

    private String pageTitle = UNTITLED;
    private String pageTitleRelative = UNTITLED;
    private String pageTitleFull = UNTITLED;
    private String rssHref = "";
    private String translationHref = "";
    private String menuMain = "index";
    private String topics;
    private Posting topicsPosting;
    private String topicsIndex;
    private boolean menuNoLogin;

    public LocationInfo(Model model) {
        this.model = model;
        try {
            initModel();
        } catch (IllegalAccessException e) {
            log.error("Error initializing model from LocationInfo", e);
        }
    }

    public LocationInfo getParent() {
        return parent;
    }

    public String getUri() {
        return uri;
    }

    public String getPageTitle() {
        return pageTitle;
    }

    public String getPageTitleRelative() {
        return pageTitleRelative;
    }

    public String getPageTitleFull() {
        return pageTitleFull;
    }

    public String getRssHref() {
        return rssHref;
    }

    public String getTranslationHref() {
        return translationHref;
    }

    public String getMenuMain() {
        return menuMain;
    }

    public String getTopics() {
        return topics;
    }

    public String getTopicsIndex() {
        return topicsIndex;
    }

    public boolean isMenuNoLogin() {
        return menuNoLogin;
    }

    private void initModel() throws IllegalAccessException {
        if (model == null) {
            return;
        }

        for (Field field : getClass().getDeclaredFields()) {
            if (field.isAnnotationPresent(NonPublished.class)) {
                continue;
            }
            model.addAttribute(field.getName(), field.get(this));
        }
    }

    public LocationInfo withParent(LocationInfo parent) {
        this.parent = parent;

        if (parent != null) {
            if (model != null) {
                model.addAttribute("parentLocation", parent);

                Deque<LocationInfo> locations = new ArrayDeque<>();
                LocationInfo current = this;
                while (current != null) {
                    locations.addFirst(current);
                    current = current.getParent();
                }
                model.addAttribute("locations", locations);
            }
            if (!StringUtils.isEmpty(parent.getRssHref()) && StringUtils.isEmpty(getRssHref())) {
                withRssHref(parent.getRssHref());
            }
        }

        return this;
    }

    public LocationInfo withUri(String uri) {
        this.uri = uri;
        return this;
    }

    private void addAttribute(String name, Object value) {
        if (model != null) {
            model.addAttribute(name, value);
        }
    }

    public LocationInfo withPageTitle(String pageTitle) {
        this.pageTitle = pageTitle;
        addAttribute("pageTitle", pageTitle);
        if (pageTitleRelative.equals(UNTITLED)) {
            withPageTitleRelative(pageTitle);
        }
        if (pageTitleFull.equals(UNTITLED)) {
            withPageTitleFull(pageTitle);
        }
        return this;
    }

    public LocationInfo withPageTitleRelative(String pageTitleRelative) {
        this.pageTitleRelative = pageTitleRelative;
        addAttribute("pageTitleRelative", pageTitleRelative);
        return this;
    }

    public LocationInfo withPageTitleFull(String pageTitleFull) {
        this.pageTitleFull = pageTitleFull;
        addAttribute("pageTitleFull", pageTitleFull);
        return this;
    }

    public LocationInfo withRssHref(String rssHref) {
        this.rssHref = rssHref;
        addAttribute("rssHref", rssHref);
        return this;
    }

    public LocationInfo withTranslationHref(String translationHref) {
        this.translationHref = translationHref;
        addAttribute("translationHref", translationHref);
        return this;
    }

    public LocationInfo withMenuMain(String menuMain) {
        this.menuMain = menuMain;
        addAttribute("menuMain", menuMain);
        return this;
    }

    public LocationInfo withTopics(String topics) {
        return withTopics(topics, null);
    }

    public LocationInfo withTopics(String topics, Posting posting) {
        this.topics = topics;
        this.topicsPosting = posting;
        addAttribute("topics", topics);
        return this;
    }

    public LocationInfo withTopicsIndex(String topicsIndex) {
        this.topicsIndex = topicsIndex;
        addAttribute("topicsIndex", topicsIndex);
        // Must be executed after setting topicsIndex, because it may be used in the TopicsMapping method
        if (model != null) {
            String mappedTopics = TopicsService.getInstance().callMapping(topics, topicsPosting, model);
            topics = mappedTopics != null ? mappedTopics : topics;
        }
        return this;
    }

    public LocationInfo withMenuNoLogin(boolean menuNoLogin) {
        this.menuNoLogin = menuNoLogin;
        addAttribute("menuNoLogin", menuNoLogin);
        return this;
    }

}