package ua.org.migdal.form;

import java.io.Serializable;

import javax.validation.constraints.Size;

import ua.org.migdal.grp.GrpDescriptor;
import ua.org.migdal.grp.GrpEnum;

public class PostingForm implements Serializable {

    private static final long serialVersionUID = 4588207664747142717L;

    private boolean full;

    private long id;

    private long personId;

    private long upId;

    private long grp;

    private String priority = "0";

    private long parentId;

    @Size(max=75)
    private String ident = ""; // empty is null

    @Size(max=250)
    private String subject = "";

    @Size(max=250)
    private String author = "";

    @Size(max=250)
    private String source = "";

    @Size(max=250)
    private String comment0 = "";

    private String body = "";

    private String index1 = "0";

    private String index2 = "0";

    @Size(max=7)
    private String lang = "";

    @Size(max=250)
    private String url = "";

    private String title = "";

    private String largeBody = "";

    private boolean hidden;

    private boolean disabled;

    public PostingForm(boolean full, long grp) {
        this.full = full;
        this.grp = grp;
    }

    public boolean isFull() {
        return full;
    }

    public void setFull(boolean full) {
        this.full = full;
    }

    public long getId() {
        return id;
    }

    public void setId(long id) {
        this.id = id;
    }

    public long getPersonId() {
        return personId;
    }

    public void setPersonId(long personId) {
        this.personId = personId;
    }

    public long getUpId() {
        return upId;
    }

    public void setUpId(long upId) {
        this.upId = upId;
    }

    public long getGrp() {
        return grp;
    }

    public void setGrp(long grp) {
        this.grp = grp;
    }

    public GrpDescriptor getGrpInfo() {
        return GrpEnum.getInstance().grp(grp);
    }

    public String getPriority() {
        return priority;
    }

    public void setPriority(String priority) {
        this.priority = priority;
    }

    public long getParentId() {
        return parentId;
    }

    public void setParentId(long parentId) {
        this.parentId = parentId;
    }

    public String getIdent() {
        return ident;
    }

    public void setIdent(String ident) {
        this.ident = ident;
    }

    public String getSubject() {
        return subject;
    }

    public void setSubject(String subject) {
        this.subject = subject;
    }

    public String getAuthor() {
        return author;
    }

    public void setAuthor(String author) {
        this.author = author;
    }

    public String getSource() {
        return source;
    }

    public void setSource(String source) {
        this.source = source;
    }

    public String getComment0() {
        return comment0;
    }

    public void setComment0(String comment0) {
        this.comment0 = comment0;
    }

    public String getBody() {
        return body;
    }

    public void setBody(String body) {
        this.body = body;
    }

    public String getIndex1() {
        return index1;
    }

    public void setIndex1(String index1) {
        this.index1 = index1;
    }

    public String getIndex2() {
        return index2;
    }

    public void setIndex2(String index2) {
        this.index2 = index2;
    }

    public String getLang() {
        return lang;
    }

    public void setLang(String lang) {
        this.lang = lang;
    }

    public String getUrl() {
        return url;
    }

    public void setUrl(String url) {
        this.url = url;
    }

    public String getTitle() {
        return title;
    }

    public void setTitle(String title) {
        this.title = title;
    }

    public String getLargeBody() {
        return largeBody;
    }

    public void setLargeBody(String largeBody) {
        this.largeBody = largeBody;
    }

    public boolean isHidden() {
        return hidden;
    }

    public void setHidden(boolean hidden) {
        this.hidden = hidden;
    }

    public boolean isDisabled() {
        return disabled;
    }

    public void setDisabled(boolean disabled) {
        this.disabled = disabled;
    }

}