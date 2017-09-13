package ua.org.migdal.form;

import java.io.Serializable;
import java.time.format.DateTimeFormatter;

import javax.validation.constraints.Size;

import org.hibernate.validator.constraints.NotBlank;
import ua.org.migdal.data.Posting;
import ua.org.migdal.grp.GrpDescriptor;
import ua.org.migdal.grp.GrpEnum;
import ua.org.migdal.text.TextFormat;
import ua.org.migdal.util.Utils;

public class PostingForm implements Serializable {

    private static final long serialVersionUID = 4588207664747142717L;

    private static final DateTimeFormatter DATE_FORMATTER = DateTimeFormatter.ofPattern("dd-MM-yyyy");
    private static final DateTimeFormatter TIME_FORMATTER = DateTimeFormatter.ofPattern("HH:mm:ss");

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

    private int bodyFormat = TextFormat.PLAIN.getValue();

    private String index1 = "0";

    private String index2 = "0";

    @Size(max=7)
    private String lang = "";

    @Size(max=250)
    private String url = "";

    private String title = "";

    private String largeBody = "";

    private int largeBodyFormat = TextFormat.PLAIN.getValue();

    @NotBlank
    private String sentDate = DATE_FORMATTER.format(Utils.now().toLocalDateTime());

    @NotBlank
    private String sentTime = TIME_FORMATTER.format(Utils.now().toLocalDateTime());

    private boolean hidden;

    private boolean disabled;

    public PostingForm() {
    }

    public PostingForm(boolean full, long grp) {
        this.full = full;
        this.grp = grp;
    }

    public PostingForm(Posting posting, boolean full) {
        this.full = full;
        id = posting.getId();
        personId = posting.getPerson() != null ? posting.getPerson().getId() : 0;
        upId = posting.getUpId();
        grp = posting.getGrp();
        priority = Short.toString(posting.getPriority());
        parentId = posting.getParentId();
        ident = posting.getIdent() != null ? posting.getIdent() : "";
        subject = posting.getSubject();
        author = posting.getAuthor();
        source = posting.getSource();
        comment0 = posting.getComment0();
        body = posting.getBody();
        bodyFormat = posting.getBodyFormat().getValue();
        index1 = Long.toString(posting.getIndex1());
        index2 = Long.toString(posting.getIndex2());
        lang = posting.getLang();
        url = posting.getUrl();
        title = posting.getTitle();
        largeBody = posting.getLargeBody();
        largeBodyFormat = posting.getLargeBodyFormat().getValue();
        sentDate = DATE_FORMATTER.format(posting.getSent().toLocalDateTime());
        sentTime = TIME_FORMATTER.format(posting.getSent().toLocalDateTime());
        hidden = posting.isHidden();
        disabled = posting.isDisabled();
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

    public int getBodyFormat() {
        return bodyFormat;
    }

    public void setBodyFormat(int bodyFormat) {
        this.bodyFormat = bodyFormat;
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

    public int getLargeBodyFormat() {
        return largeBodyFormat;
    }

    public void setLargeBodyFormat(int largeBodyFormat) {
        this.largeBodyFormat = largeBodyFormat;
    }

    public String getSentDate() {
        return sentDate;
    }

    public void setSentDate(String sentDate) {
        this.sentDate = sentDate;
    }

    public String getSentTime() {
        return sentTime;
    }

    public void setSentTime(String sentTime) {
        this.sentTime = sentTime;
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