package ua.org.migdal.form;

import java.io.Serializable;
import java.sql.Timestamp;
import java.time.LocalDate;
import java.time.LocalDateTime;
import java.time.LocalTime;
import java.time.ZoneOffset;
import java.time.format.DateTimeFormatter;

import javax.validation.constraints.Size;

import org.hibernate.validator.constraints.NotBlank;
import org.springframework.util.StringUtils;

import ua.org.migdal.data.Entry;
import ua.org.migdal.data.Posting;
import ua.org.migdal.data.Topic;
import ua.org.migdal.data.User;
import ua.org.migdal.grp.GrpDescriptor;
import ua.org.migdal.grp.GrpEnum;
import ua.org.migdal.mtext.MtextFormat;
import ua.org.migdal.session.RequestContext;
import ua.org.migdal.text.Text;
import ua.org.migdal.text.TextFormat;
import ua.org.migdal.util.Perm;
import ua.org.migdal.util.UriUtils;
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

    public boolean isMandatory(String field) {
        return getGrpInfo().isMandatory(field);
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

    private Timestamp getSent() {
        if (!StringUtils.isEmpty(getSentDate())) {
            try {
                LocalDate sentDate = LocalDate.parse(getSentDate(), DATE_FORMATTER);
                LocalTime sentTime = LocalTime.MIDNIGHT;
                if (!StringUtils.isEmpty(getSentTime())) {
                    sentTime = LocalTime.parse(getSentTime(), TIME_FORMATTER);
                }
                return Timestamp.from(LocalDateTime.of(sentDate, sentTime).toInstant(ZoneOffset.UTC));
            } catch (Exception e) {
            }
        }
        return Utils.now();
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

    public void toPosting(Posting posting, Entry up, Topic parent, User person, RequestContext requestContext) {
        posting.setBodyFormat(TextFormat.valueOf(getBodyFormat(), TextFormat.PLAIN));
        posting.setBody(Text.convertLigatures(getBody()));
        posting.setBodyXml(Text.convert(posting.getBody(), posting.getBodyFormat(), MtextFormat.SHORT));
        posting.setLargeBodyFormat(TextFormat.valueOf(getLargeBodyFormat(), TextFormat.PLAIN));
        posting.setHasLargeBody(false);
        if (!StringUtils.isEmpty(getLargeBody())) {
            posting.setHasLargeBody(true);
            posting.setLargeBody(Text.convertLigatures(getLargeBody()));
            posting.setLargeBodyXml(
                    Text.convert(posting.getLargeBody(), posting.getLargeBodyFormat(), MtextFormat.SHORT));
        }
        /*$this->small_image = $vars['small_image'];
        $this->small_image_x = $vars['small_image_x'];
        $this->small_image_y = $vars['small_image_y'];
        $this->small_image_format = $vars['small_image_format'];
        $this->large_image = $vars['large_image'];
        $this->large_image_x = $vars['large_image_x'];
        $this->large_image_y = $vars['large_image_y'];
        $this->large_image_size = $vars['large_image_size'];
        $this->large_image_format = $vars['large_image_format'];
        $this->large_image_filename = $vars['large_image_filename'];*/
        posting.setUp(up != null && up.getId() > 0 ? up : null);
        posting.setSubject(Text.convertLigatures(getSubject()));
        posting.setComment0(Text.convertLigatures(getComment0()));
        posting.setComment0Xml(Text.convert(posting.getComment0(), posting.getBodyFormat(), MtextFormat.LINE));
        posting.setAuthor(Text.convertLigatures(getAuthor()));
        posting.setAuthorXml(Text.convert(posting.getAuthor(), posting.getBodyFormat(), MtextFormat.LINE));
        posting.setSource(Text.convertLigatures(getSource()));
        posting.setSourceXml(Text.convert(posting.getSource(), posting.getBodyFormat(), MtextFormat.LINE));
        posting.setTitle(Text.convertLigatures(getTitle()));
        posting.setTitleXml(Text.convert(posting.getTitle(), posting.getBodyFormat(), MtextFormat.LINE));
        //$this->guest_login = isset($vars['guest_login']) ? $vars['guest_login'] : '';
        if (isHidden()) {
            posting.setPerms(posting.getPerms() & ~(Perm.OR | Perm.ER));
        } else {
            posting.setPerms(posting.getPerms() | Perm.OR | Perm.ER);
        }
        posting.setLang(getLang());
        if (!StringUtils.isEmpty(getUrl()) && !getUrl().contains("://") && !getUrl().startsWith("/")) {
            posting.setUrl(String.format("http://%s", getUrl())); // FIXME maybe https?
        } else {
            posting.setUrl(getUrl());
        }
        posting.setUrlDomain(UriUtils.getUrlDomain(posting.getUrl()));
        posting.setIndex1(Utils.toLong(getIndex1(), 0L));
        posting.setIndex2(Utils.toLong(getIndex2(), 0L));
        posting.setParent(parent != null && parent.getId() > 0 ? parent : null);
        posting.setGrp(getGrp());
        posting.setPerson(person != null && person.getId() > 0 ? person : null);
        if (requestContext.isUserModerator()) {
            posting.setIdent(!StringUtils.isEmpty(getIdent()) ? getIdent() : null);
            posting.setDisabled(isDisabled());
            posting.setPriority(Utils.toShort(getPriority(), (short) 0));
        }
        if (getId() <= 0 || requestContext.isUserModerator()) {
            posting.setSent(getSent());
        }
        posting.setModifier(requestContext.getUser());
        posting.setModified(Utils.now());
        if (getId() <= 0) {
            posting.setCreator(requestContext.getUser());
            posting.setCreated(Utils.now());
        }
    }

}