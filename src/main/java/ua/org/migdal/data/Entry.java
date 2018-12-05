package ua.org.migdal.data;

import java.sql.Timestamp;

import javax.persistence.Access;
import javax.persistence.AccessType;
import javax.persistence.Column;
import javax.persistence.DiscriminatorColumn;
import javax.persistence.DiscriminatorType;
import javax.persistence.Entity;
import javax.persistence.Enumerated;
import javax.persistence.FetchType;
import javax.persistence.GeneratedValue;
import javax.persistence.Id;
import javax.persistence.Inheritance;
import javax.persistence.InheritanceType;
import javax.persistence.JoinColumn;
import javax.persistence.ManyToOne;
import javax.persistence.PreRemove;
import javax.persistence.Table;
import javax.persistence.Transient;
import javax.validation.constraints.NotNull;
import javax.validation.constraints.Size;

import ua.org.migdal.Config;
import ua.org.migdal.data.util.TreeElement;
import ua.org.migdal.mtext.Mtext;
import ua.org.migdal.mtext.MtextFormat;
import ua.org.migdal.session.RequestContextImpl;
import ua.org.migdal.text.TextFormat;
import ua.org.migdal.util.CatalogUtils;
import ua.org.migdal.util.ImageFileUtils;
import ua.org.migdal.util.Perm;
import ua.org.migdal.util.PermUtils;
import ua.org.migdal.util.TrackUtils;

@Entity
@Table(name="entries")
@Inheritance(strategy=InheritanceType.SINGLE_TABLE)
@DiscriminatorColumn(name="entry", discriminatorType=DiscriminatorType.INTEGER)
public class Entry implements TreeElement {

    @Id
    @GeneratedValue
    @Access(AccessType.PROPERTY)
    private long id;

    @Size(max=75)
    private String ident;

    @Enumerated
    @Column(name = "entry", insertable = false, updatable = false)
    private EntryType entryType;

    @ManyToOne(fetch=FetchType.LAZY)
    @JoinColumn(name="up")
    private Entry up;

    @NotNull
    @Size(max=255)
    private String track = "";

    @NotNull
    @Size(max=255)
    private String catalog = "";

    @ManyToOne
    private Entry parent;

    @ManyToOne
    private Entry orig;

    @ManyToOne(fetch=FetchType.LAZY)
    private Entry current;

    @NotNull
    private long grp;

    @ManyToOne
    private User person;

    @NotNull
    @Size(max=30)
    private String guestLogin = "";

    @ManyToOne
    private User user;

    @ManyToOne
    private User group;

    @NotNull
    private long perms;

    @NotNull
    private boolean disabled;

    @NotNull
    @Size(max=255)
    private String subject = "";

    @NotNull
    @Size(max=7)
    private String lang = "";

    @NotNull
    @Size(max=255)
    private String author = "";

    @NotNull
    @Size(max=255)
    private String authorXml = "";

    @NotNull
    @Size(max=255)
    private String source = "";

    @NotNull
    @Size(max=255)
    private String sourceXml = "";

    @NotNull
    @Size(max=255)
    private String title = "";

    @NotNull
    @Size(max=255)
    private String titleXml = "";

    @NotNull
    @Size(max=255)
    private String comment0 = "";

    @NotNull
    @Size(max=255)
    @Column(name="comment0_xml")
    private String comment0Xml = "";

    @NotNull
    @Size(max=255)
    private String comment1 = "";

    @NotNull
    @Size(max=255)
    @Column(name="comment1_xml")
    private String comment1Xml = "";

    @NotNull
    @Size(max=255)
    private String url = "";

    @NotNull
    @Size(max=70)
    private String urlDomain = "";

    private Timestamp urlCheck;

    private Timestamp urlCheckSuccess;

    @NotNull
    private String body = "";

    @NotNull
    private String bodyXml = "";

    @NotNull
    @Enumerated
    private TextFormat bodyFormat = TextFormat.PLAIN;

    @NotNull
    private boolean hasLargeBody;

    @NotNull
    private String largeBody = "";

    @NotNull
    private String largeBodyXml = "";

    @NotNull
    @Enumerated
    private TextFormat largeBodyFormat = TextFormat.PLAIN;

    @NotNull
    @Size(max=70)
    private String largeBodyFilename = "";

    @NotNull
    private short priority;

    @NotNull
    private long index0;

    @NotNull
    private long index1;

    @NotNull
    private long index2;

    @NotNull
    private long set0;

    @NotNull
    @Column(name="set0_index")
    private long set0Index;

    @NotNull
    private long set1;

    @NotNull
    @Column(name="set1_index")
    private long set1Index;

    @NotNull
    private int vote;

    @NotNull
    private int voteCount;

    @NotNull
    private double rating;

    @NotNull
    private int counter0;

    @NotNull
    private int counter1;

    @NotNull
    private int counter2;

    @NotNull
    private int counter3;

    @NotNull
    private double ratio;

    private Timestamp sent;

    private Timestamp created;

    private Timestamp modified;

    private Timestamp accessed;

    @ManyToOne(fetch=FetchType.LAZY)
    private User creator;

    @ManyToOne(fetch=FetchType.LAZY)
    private User modifier;

    @NotNull
    private long modbits;

    @NotNull
    private long comments;

    @Column(name="last_comment")
    private Timestamp lastCommentTimestamp;

    @ManyToOne(fetch=FetchType.LAZY)
    private Comment lastComment;

    @ManyToOne(fetch=FetchType.LAZY)
    private User lastCommentUser;

    @NotNull
    @Size(max=30)
    private String lastCommentGuestLogin = "";

    @ManyToOne(fetch=FetchType.LAZY)
    @JoinColumn(name="small_image")
    private ImageFile smallImage;

    @NotNull
    @Column(name="small_image_x")
    private short smallImageX;

    @NotNull
    @Column(name="small_image_y")
    private short smallImageY;

    @NotNull
    @Size(max=30)
    private String smallImageFormat = "";

    @ManyToOne(fetch=FetchType.LAZY)
    @JoinColumn(name="large_image")
    private ImageFile largeImage;

    @NotNull
    @Column(name="large_image_x")
    private short largeImageX;

    @NotNull
    @Column(name="large_image_y")
    private short largeImageY;

    @NotNull
    private long largeImageSize;

    @NotNull
    @Size(max=30)
    private String largeImageFormat = "";

    @NotNull
    @Size(max=70)
    private String largeImageFilename = "";

    public long getId() {
        return id;
    }

    public void setId(long id) {
        this.id = id;
    }

    public String getIdent() {
        return ident;
    }

    public void setIdent(String ident) {
        this.ident = ident;
    }

    public EntryType getEntryType() {
        return entryType;
    }

    public Entry getUp() {
        return up;
    }

    public void setUp(Entry up) {
        this.up = up;
    }

    @Transient
    public long getUpId() {
        return getUp() != null ? getUp().getId() : 0;
    }

    public String getTrack() {
        return track;
    }

    public void setTrack(String track) {
        this.track = track;
    }

    @Transient
    public String getTrackPath() {
        return TrackUtils.toPath(getTrack());
    }

    @Transient
    public String getParentTrackPath() {
        return TrackUtils.toPath(getTrack(), 1);
    }

    public String getCatalog() {
        return catalog;
    }

    public void setCatalog(String catalog) {
        this.catalog = catalog;
    }

    public String getCatalog(int start) {
        return getCatalog(start, 1);
    }

    public String getCatalog(int start, int length) {
        return CatalogUtils.sub(catalog, start, length);
    }

    @Transient
    public String getHref() {
        return "/" + getCatalog();
    }

    public Entry getParent() {
        return parent;
    }

    public void setParent(Entry parent) {
        this.parent = parent;
    }

    @Transient
    public long getParentId() {
        return getParent() != null ? getParent().getId() : 0;
    }

    public Entry getOrig() {
        return orig;
    }

    public void setOrig(Entry orig) {
        this.orig = orig;
    }

    @Transient
    public long getOrigId() {
        return getOrig() != null ? getOrig().getId() : 0;
    }

    @Transient
    public boolean isShadow() {
        return getId() != getOrigId();
    }

    public Entry getCurrent() {
        return current;
    }

    public void setCurrent(Entry current) {
        this.current = current;
    }

    public long getGrp() {
        return grp;
    }

    public void setGrp(long grp) {
        this.grp = grp;
    }

    public User getPerson() {
        return person;
    }

    public void setPerson(User person) {
        this.person = person;
    }

    public String getGuestLogin() {
        return guestLogin;
    }

    public void setGuestLogin(String guestLogin) {
        this.guestLogin = guestLogin;
    }

    public User getUser() {
        return user;
    }

    public void setUser(User user) {
        this.user = user;
    }

    @Transient
    public String getUserLogin() {
        return getUser() != null ? getUser().getLogin() : "";
    }

    @Transient
    public String getUserFolder() {
        return getUser() != null ? getUser().getFolder() : "";
    }

    @Transient
    public boolean isUserHidden() {
        return getUser().getHidden() > 0;
    }

    @Transient
    public boolean isUserAdminHidden() {
        return getUser().getHidden() > 1;
    }

    @Transient
    public boolean isUserVisible() {
        return getUser().isVisible();
    }

    @Transient
    public boolean isUserGuest() {
        return getUser() == null || getUser().isGuest();
    }

    public User getGroup() {
        return group;
    }

    public void setGroup(User group) {
        this.group = group;
    }

    public long getPerms() {
        return perms;
    }

    public void setPerms(long perms) {
        this.perms = perms;
    }

    @Transient
    public String getPermString() {
        return PermUtils.toString(getPerms());
    }

    protected boolean isPermitted(long right) {
        return PermUtils.perm(getUser().getId(), getGroup().getId(), getPerms(), right,
                RequestContextImpl.getInstance());
    }

    @Transient
    public boolean isReadable() {
        return isPermitted(Perm.READ);
    }

    @Transient
    public boolean isWritable() {
        return isPermitted(Perm.WRITE);
    }

    @Transient
    public boolean isAppendable() {
        return isPermitted(Perm.APPEND);
    }

    public boolean isAnyAppendable() {
        return (getPerms() & (Perm.UA | Perm.GA | Perm.OA | Perm.EA)) != 0;
    }

    @Transient
    public boolean isPostable() {
        return isPermitted(Perm.POST);
    }

    @Transient
    public boolean isAnyPostable() {
        return (getPerms() & (Perm.UP | Perm.GP | Perm.OP | Perm.EP)) != 0;
    }

    @Transient
    public boolean isGuestPostable() {
        return (getPerms() & Perm.EP) != 0;
    }

    @Transient
    public boolean isHidden() {
        return (getPerms() & (Perm.OR | Perm.ER)) == 0;
    }

    @Transient
    public void setHidden(boolean hidden) {
        if (hidden) {
            setPerms(getPerms() & ~Perm.OR & ~Perm.ER);
        } else {
            setPerms(getPerms() | Perm.OR | Perm.ER);
        }
    }

    public boolean isDisabled() {
        return disabled;
    }

    public void setDisabled(boolean disabled) {
        this.disabled = disabled;
    }

    public String getSubject() {
        return subject;
    }

    public void setSubject(String subject) {
        this.subject = subject;
    }

    public String getLang() {
        return lang;
    }

    public void setLang(String lang) {
        this.lang = lang;
    }

    public String getAuthor() {
        return author;
    }

    public void setAuthor(String author) {
        this.author = author;
    }

    public String getAuthorXml() {
        return authorXml;
    }

    public void setAuthorXml(String authorXml) {
        this.authorXml = authorXml;
    }

    @Transient
    public Mtext getAuthorMtext() {
        return new Mtext(getAuthorXml(), MtextFormat.LINE, getId());
    }

    public String getSource() {
        return source;
    }

    public void setSource(String source) {
        this.source = source;
    }

    public String getSourceXml() {
        return sourceXml;
    }

    public void setSourceXml(String sourceXml) {
        this.sourceXml = sourceXml;
    }

    @Transient
    public Mtext getSourceMtext() {
        return new Mtext(getSourceXml(), MtextFormat.LINE, getId());
    }

    public String getTitle() {
        return title;
    }

    public void setTitle(String title) {
        this.title = title;
    }

    public String getTitleXml() {
        return titleXml;
    }

    public void setTitleXml(String titleXml) {
        this.titleXml = titleXml;
    }

    @Transient
    public Mtext getTitleMtext() {
        return new Mtext(getTitleXml(), MtextFormat.SHORT, getId());
    }

    @Transient
    public Mtext getTitleLineMtext() {
        return new Mtext(getTitleXml(), MtextFormat.LINE, getId());
    }

    @Transient
    public String getTitleTiny() {
        Config config = Config.getInstance();

        return getTitleMtext().shortenNote(config.getTinySize(), config.getTinySizeMinus(), config.getTinySizePlus());
    }

    public String getComment0() {
        return comment0;
    }

    public void setComment0(String comment0) {
        this.comment0 = comment0;
    }

    public String getComment0Xml() {
        return comment0Xml;
    }

    public void setComment0Xml(String comment0Xml) {
        this.comment0Xml = comment0Xml;
    }

    @Transient
    public Mtext getComment0Mtext() {
        return new Mtext(getComment0Xml(), MtextFormat.SHORT, getId());
    }

    public String getComment1() {
        return comment1;
    }

    public void setComment1(String comment1) {
        this.comment1 = comment1;
    }

    public String getComment1Xml() {
        return comment1Xml;
    }

    public void setComment1Xml(String comment1Xml) {
        this.comment1Xml = comment1Xml;
    }

    @Transient
    public Mtext getComment1Mtext() {
        return new Mtext(getComment1Xml(), MtextFormat.SHORT, getId());
    }

    public String getUrl() {
        return url;
    }

    public void setUrl(String url) {
        this.url = url;
    }

    public String getUrlDomain() {
        return urlDomain;
    }

    public void setUrlDomain(String urlDomain) {
        this.urlDomain = urlDomain;
    }

    public Timestamp getUrlCheck() {
        return urlCheck;
    }

    public void setUrlCheck(Timestamp urlCheck) {
        this.urlCheck = urlCheck;
    }

    public Timestamp getUrlCheckSuccess() {
        return urlCheckSuccess;
    }

    public void setUrlCheckSuccess(Timestamp urlCheckSuccess) {
        this.urlCheckSuccess = urlCheckSuccess;
    }

    public String getBody() {
        return body;
    }

    public void setBody(String body) {
        this.body = body;
    }

    public String getBodyXml() {
        return bodyXml;
    }

    public void setBodyXml(String bodyXml) {
        this.bodyXml = bodyXml;
    }

    @Transient
    public Mtext getBodyMtext() {
        return new Mtext(getBodyXml(), MtextFormat.SHORT, getId());
    }

    @Transient
    public String getBodyNormal() {
        return getBodyMtext().shortenNote(65535, 0, 0);
    }

    @Transient
    public boolean isBodyShorterThanTiny() {
        Config config = Config.getInstance();

        return getBodyMtext().cleanLength() <= config.getTinySize() + config.getTinySizePlus();
    }

    @Transient
    public String getBodyTiny() {
        Config config = Config.getInstance();

        return getBodyMtext().shortenNote(config.getTinySize(), config.getTinySizeMinus(), config.getTinySizePlus());
    }

    @Transient
    public Mtext getBodyTinyMtext() {
        Config config = Config.getInstance();

        return getBodyMtext().shorten(config.getTinySize(), config.getTinySizeMinus(), config.getTinySizePlus());
    }

    @Transient
    public boolean isBodyShorterThanSmall() {
        Config config = Config.getInstance();

        return getBodyMtext().cleanLength() <= config.getSmallSize() + config.getSmallSizePlus();
    }

    @Transient
    public Mtext getBodySmallMtext() {
        Config config = Config.getInstance();

        return getBodyMtext().shorten(config.getSmallSize(), config.getSmallSizeMinus(), config.getSmallSizePlus());
    }

    @Transient
    public boolean isBodyShorterThanMedium() {
        Config config = Config.getInstance();

        return getBodyMtext().cleanLength() <= config.getMediumSize() + config.getMediumSizePlus();
    }

    @Transient
    public String getBodyMedium() {
        Config config = Config.getInstance();

        return getBodyMtext().shortenNote(config.getMediumSize(), config.getMediumSizeMinus(),
                config.getMediumSizePlus());
    }

    @Transient
    public Mtext getBodyMediumMtext() {
        Config config = Config.getInstance();

        return getBodyMtext().shorten(config.getMediumSize(), config.getMediumSizeMinus(), config.getMediumSizePlus());
    }

    public TextFormat getBodyFormat() {
        return bodyFormat;
    }

    public void setBodyFormat(TextFormat bodyFormat) {
        this.bodyFormat = bodyFormat;
    }

    public boolean isHasLargeBody() {
        return hasLargeBody;
    }

    public void setHasLargeBody(boolean hasLargeBody) {
        this.hasLargeBody = hasLargeBody;
    }

    public String getLargeBody() {
        return largeBody;
    }

    public void setLargeBody(String largeBody) {
        this.largeBody = largeBody;
    }

    public String getLargeBodyXml() {
        return largeBodyXml;
    }

    public void setLargeBodyXml(String largeBodyXml) {
        this.largeBodyXml = largeBodyXml;
    }

    public TextFormat getLargeBodyFormat() {
        return largeBodyFormat;
    }

    public void setLargeBodyFormat(TextFormat largeBodyFormat) {
        this.largeBodyFormat = largeBodyFormat;
    }

    @Transient
    public Mtext getLargeBodyMtext() {
        return new Mtext(getLargeBodyXml(), MtextFormat.LONG, getId());
    }

    public String getLargeBodyFilename() {
        return largeBodyFilename;
    }

    public void setLargeBodyFilename(String largeBodyFilename) {
        this.largeBodyFilename = largeBodyFilename;
    }

    public short getPriority() {
        return priority;
    }

    public void setPriority(short priority) {
        this.priority = priority;
    }

    public long getIndex0() {
        return index0;
    }

    public void setIndex0(long index0) {
        this.index0 = index0;
    }

    public long getIndex1() {
        return index1;
    }

    public void setIndex1(long index1) {
        this.index1 = index1;
    }

    public long getIndex2() {
        return index2;
    }

    public void setIndex2(long index2) {
        this.index2 = index2;
    }

    public long getSet0() {
        return set0;
    }

    public void setSet0(long set0) {
        this.set0 = set0;
    }

    public long getSet0Index() {
        return set0Index;
    }

    public void setSet0Index(long set0Index) {
        this.set0Index = set0Index;
    }

    public long getSet1() {
        return set1;
    }

    public void setSet1(long set1) {
        this.set1 = set1;
    }

    public long getSet1Index() {
        return set1Index;
    }

    public void setSet1Index(long set1Index) {
        this.set1Index = set1Index;
    }

    public int getVote() {
        return vote;
    }

    public void setVote(int vote) {
        this.vote = vote;
    }

    public int getVoteCount() {
        return voteCount;
    }

    public void setVoteCount(int voteCount) {
        this.voteCount = voteCount;
    }

    public double getRating() {
        return rating;
    }

    public void setRating(double rating) {
        this.rating = rating;
    }

    public int getCounter0() {
        return counter0;
    }

    public void setCounter0(int counter0) {
        this.counter0 = counter0;
    }

    public int getCounter1() {
        return counter1;
    }

    public void setCounter1(int counter1) {
        this.counter1 = counter1;
    }

    public int getCounter2() {
        return counter2;
    }

    public void setCounter2(int counter2) {
        this.counter2 = counter2;
    }

    public int getCounter3() {
        return counter3;
    }

    public void setCounter3(int counter3) {
        this.counter3 = counter3;
    }

    public double getRatio() {
        return ratio;
    }

    public void setRatio(double ratio) {
        this.ratio = ratio;
    }

    @Transient
    public String getRatingString() {
        return String.format("%1.2f", getRating());
    }

    @Transient
    public Long getRatingLong() {
        return (long) getRating();
    }

    public Timestamp getSent() {
        return sent;
    }

    public void setSent(Timestamp sent) {
        this.sent = sent;
    }

    public Timestamp getCreated() {
        return created;
    }

    public void setCreated(Timestamp created) {
        this.created = created;
    }

    public Timestamp getModified() {
        return modified;
    }

    public void setModified(Timestamp modified) {
        this.modified = modified;
    }

    public Timestamp getAccessed() {
        return accessed;
    }

    public void setAccessed(Timestamp accessed) {
        this.accessed = accessed;
    }

    public User getCreator() {
        return creator;
    }

    public void setCreator(User creator) {
        this.creator = creator;
    }

    public User getModifier() {
        return modifier;
    }

    public void setModifier(User modifier) {
        this.modifier = modifier;
    }

    public long getModbits() {
        return modbits;
    }

    public void setModbits(long modbits) {
        this.modbits = modbits;
    }

    public boolean hasModbit(Modbit modbit) {
        return (getModbits() & modbit.getValue()) != 0;
    }

    public void setModbit(Modbit modbit) {
        setModbits(getModbits() | modbit.getValue());
    }

    public long getComments() {
        return comments;
    }

    public void setComments(long comments) {
        this.comments = comments;
    }

    public Timestamp getLastCommentTimestamp() {
        return lastCommentTimestamp;
    }

    public void setLastCommentTimestamp(Timestamp lastCommentTimestamp) {
        this.lastCommentTimestamp = lastCommentTimestamp;
    }

    public Comment getLastComment() {
        return lastComment;
    }

    public void setLastComment(Comment lastComment) {
        this.lastComment = lastComment;
    }

    public User getLastCommentUser() {
        return lastCommentUser;
    }

    public void setLastCommentUser(User lastCommentUser) {
        this.lastCommentUser = lastCommentUser;
    }

    public String getLastCommentGuestLogin() {
        return lastCommentGuestLogin;
    }

    public void setLastCommentGuestLogin(String lastCommentGuestLogin) {
        this.lastCommentGuestLogin = lastCommentGuestLogin;
    }

    public ImageFile getSmallImage() {
        return smallImage;
    }

    public void setSmallImage(ImageFile smallImage) {
        this.smallImage = smallImage;
    }

    @Transient
    public boolean isHasSmallImage() {
        return getSmallImage() != null;
    }

    @Transient
    public boolean isHasImage() {
        return isHasSmallImage();
    }

    public short getSmallImageX() {
        return smallImageX;
    }

    public void setSmallImageX(short smallImageX) {
        this.smallImageX = smallImageX;
    }

    public short getSmallImageY() {
        return smallImageY;
    }

    public void setSmallImageY(short smallImageY) {
        this.smallImageY = smallImageY;
    }

    public String getSmallImageFormat() {
        return smallImageFormat;
    }

    public void setSmallImageFormat(String smallImageFormat) {
        this.smallImageFormat = smallImageFormat;
    }

    @Transient
    public String getSmallImageUrl() {
        if (getSmallImage() == null) {
            return null;
        }
        return ImageFileUtils.imageUrl(getSmallImageFormat(), getSmallImage().getId());
    }

    public ImageFile getLargeImage() {
        return largeImage;
    }

    public void setLargeImage(ImageFile largeImage) {
        this.largeImage = largeImage;
    }

    @Transient
    public boolean isHasLargeImage() {
        return getLargeImage() != null
                && (getSmallImage() == null || getLargeImage().getId() != getSmallImage().getId());
    }

    public short getLargeImageX() {
        return largeImageX;
    }

    public void setLargeImageX(short largeImageX) {
        this.largeImageX = largeImageX;
    }

    @Transient
    public short getImageX() {
        return getLargeImageX();
    }

    public short getLargeImageY() {
        return largeImageY;
    }

    public void setLargeImageY(short largeImageY) {
        this.largeImageY = largeImageY;
    }

    @Transient
    public short getImageY() {
        return getLargeImageY();
    }

    public long getLargeImageSize() {
        return largeImageSize;
    }

    public void setLargeImageSize(long largeImageSize) {
        this.largeImageSize = largeImageSize;
    }

    @Transient
    public long getLargeImageSizeKb() {
        return (long) Math.ceil(getLargeImageSize() / 1024f);
    }

    @Transient
    public long getImageSize() {
        return getLargeImageSize();
    }

    @Transient
    public long getImageSizeKb() {
        return getLargeImageSizeKb();
    }

    public String getLargeImageFormat() {
        return largeImageFormat;
    }

    public void setLargeImageFormat(String largeImageFormat) {
        this.largeImageFormat = largeImageFormat;
    }

    public String getLargeImageFilename() {
        return largeImageFilename;
    }

    public void setLargeImageFilename(String largeImageFilename) {
        this.largeImageFilename = largeImageFilename;
    }

    @Transient
    public String getLargeImageUrl() {
        if (getLargeImage() == null) {
            return null;
        }
        return ImageFileUtils.imageUrl(getLargeImageFormat(), getLargeImage().getId());
    }

    @Transient
    public String getImageUrl() {
        return getLargeImageUrl();
    }

    @PreRemove
    public void preRemove() {
        if (getParent() != null
                && getParent().getLastComment() != null
                && getParent().getLastComment().getId() == getId()) {
            getParent().setLastComment(null);
        }
    }

    public static String validateHierarchy(Entry parent, Entry up, long id) {
        if (parent != null && up == null) {
            return "hierarchyNotUpUnderParent";
        }
        String parentTrack = parent != null ? parent.getTrack() : "";
        String upTrack = up != null ? up.getTrack() : "";
        if (!upTrack.startsWith(parentTrack)) {
            return "hierarchyNotUpUnderParent";
        }
        if (upTrack.contains(TrackUtils.track(id))) {
            return "hierarchyLoop";
        }
        return null;
    }

}