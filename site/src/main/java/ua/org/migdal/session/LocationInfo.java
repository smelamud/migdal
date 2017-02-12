package ua.org.migdal.session;

import java.lang.reflect.Field;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.ui.Model;

public class LocationInfo {

    private static final Logger log = LoggerFactory.getLogger(LocationInfo.class);

    private Model model;
    private LocationInfo parent;

    private String pageTitle = "";
    private boolean metaNoIndex;
    private String rssHref = "";
    private String translationHref = "";
    private String menuMain = "index";
    private String menuElement;
    private String menuIndex;

    public LocationInfo getParent() {
        return parent;
    }

    public String getPageTitle() {
        return pageTitle;
    }

    public boolean isMetaNoIndex() {
        return metaNoIndex;
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

    public String getMenuElement() {
        return menuElement;
    }

    public String getMenuIndex() {
        return menuIndex;
    }

    public LocationInfo(Model model) {
        this.model = model;
        try {
            initModel();
        } catch (IllegalAccessException e) {
            log.error("Error initializing model from LocationInfo", e);
        }
    }

    private void initModel() throws IllegalAccessException {
        if (model == null) {
            return;
        }

        for (Field field : getClass().getDeclaredFields()) {
            if (field.getName().equals("model") || field.getName().equals("parent")) {
                continue;
            }
            model.addAttribute(field.getName(), field.get(this));
        }
    }

    public LocationInfo withParent(LocationInfo parent) {
        this.parent = parent;
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
        return this;
    }

    public LocationInfo withMetaNoIndex(boolean metaNoIndex) {
        this.metaNoIndex = metaNoIndex;
        addAttribute("metaNoIndex", metaNoIndex);
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

    public LocationInfo withMenuElement(String menuElement) {
        this.menuElement = menuElement;
        addAttribute("menuElement", menuElement);
        return this;
    }

    public LocationInfo withMenuIndex(String menuIndex) {
        this.menuIndex = menuIndex;
        addAttribute("menuIndex", menuIndex);
        return this;
    }

}