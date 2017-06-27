package ua.org.migdal.grp;

import java.util.Collections;
import java.util.List;

public class GrpDescriptor {

    private String name;
    private short bit;
    private List<String> groups;

    private String rootIdent;

    /**
     * Grp used to publish this postings as collection (for example,
     * publishing albums of pictures
     */
    private String publishGrp;

    private String heading = "${subject}";

    /**
     * Page to show the posting's topic ("general view")
     */
    private String generalHref = "";

    /**
     * Page to show the posting's details ("best view")
     */
    private String detailsHref = "";

    /**
     * Template to show the posting's details
     */
    private String detailsTemplate = "posting";

    /**
     * Helper to show the posting in a feed
     */
    private String feedHelper = "postingGeneral";

    /**
     * Допустимые сообщения: {}
     */
    private String title = "Сообщения";

    /**
     * Добавить {}
     * Изменить {}
     */
    private String what = "сообщение";

    /**
     * Добавление {}
     * Редактирование {}
     */
    private String whatA = "сообщения";

    private List<GrpEditor> editors = Collections.emptyList();

    public GrpDescriptor() {
    }

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }

    public short getBit() {
        return bit;
    }

    public void setBit(short bit) {
        this.bit = bit;
    }

    public long getValue() {
        return 1L << getBit();
    }

    public List<String> getGroups() {
        return groups;
    }

    public void setGroups(List<String> groups) {
        this.groups = groups;
    }

    public String getRootIdent() {
        return rootIdent;
    }

    public void setRootIdent(String rootIdent) {
        this.rootIdent = rootIdent;
    }

    public String getPublishGrp() {
        return publishGrp;
    }

    public void setPublishGrp(String publishGrp) {
        this.publishGrp = publishGrp;
    }

    public String getHeading() {
        return heading;
    }

    public void setHeading(String heading) {
        this.heading = heading;
    }

    public String getGeneralHref() {
        return generalHref;
    }

    public void setGeneralHref(String generalHref) {
        this.generalHref = generalHref;
    }

    public String getDetailsHref() {
        return detailsHref;
    }

    public void setDetailsHref(String detailsHref) {
        this.detailsHref = detailsHref;
    }

    public String getDetailsTemplate() {
        return detailsTemplate;
    }

    public void setDetailsTemplate(String detailsTemplate) {
        this.detailsTemplate = detailsTemplate;
    }

    public String getFeedHelper() {
        return feedHelper;
    }

    public void setFeedHelper(String feedHelper) {
        this.feedHelper = feedHelper;
    }

    public String getTitle() {
        return title;
    }

    public void setTitle(String title) {
        this.title = title;
    }

    public String getWhat() {
        return what;
    }

    public void setWhat(String what) {
        this.what = what;
    }

    public String getWhatA() {
        return whatA;
    }

    public void setWhatA(String whatA) {
        this.whatA = whatA;
    }

    public List<GrpEditor> getEditors() {
        return editors;
    }

    public void setEditors(List<GrpEditor> editors) {
        this.editors = editors;
    }

}