package ua.org.migdal.form;

import java.io.Serializable;
import java.util.Arrays;
import java.util.List;
import java.util.stream.Collectors;

import javax.validation.constraints.NotBlank;
import javax.validation.constraints.Size;

import org.springframework.util.StringUtils;

import ua.org.migdal.data.Topic;
import ua.org.migdal.data.TopicModbit;
import ua.org.migdal.data.User;
import ua.org.migdal.data.util.Selected;
import ua.org.migdal.grp.GrpEnum;
import ua.org.migdal.mtext.MtextFormat;
import ua.org.migdal.session.RequestContext;
import ua.org.migdal.text.Text;
import ua.org.migdal.text.TextFormat;
import ua.org.migdal.util.PermUtils;
import ua.org.migdal.util.Utils;

public class TopicForm implements Serializable {

    private static final long serialVersionUID = -6998221348586905382L;

    private long id;

    private long upId;

    @Size(max=75)
    private String ident = "";

    @NotBlank
    @Size(max=70)
    private String subject = "";

    @Size(max=70)
    private String comment0 = "";

    @Size(max=70)
    private String comment1 = "";

    @Size(max=6)
    private String year = "";

    @Size(max=4096)
    private String body = "";

    @NotBlank
    @Size(max=30)
    private String userName = "";

    @NotBlank
    @Size(max=30)
    private String groupName = "";

    @NotBlank
    @Size(max=17)
    private String permString = "";

    private long[] grps = new long[0];

    private long[] modbits = new long[0];

    public TopicForm() {
    }

    public TopicForm(Topic topic) {
        if (topic == null) {
            return;
        }

        id = topic.getId();
        upId = topic.getUpId();
        ident = topic.getIdent() != null ? topic.getIdent() : "";
        subject = topic.getSubject();
        comment0 = topic.getComment0();
        comment1 = topic.getComment1();
        year = Long.toString(topic.getIndex2());
        body = topic.getBody();
        userName = topic.getUser().getLogin();
        groupName = topic.getGroup().getLogin();
        permString = topic.getPermString();
        grps = GrpEnum.getInstance().parse(topic.getGrp());
        modbits = TopicModbit.parse(topic.getModbits());
    }

    public long getId() {
        return id;
    }

    public void setId(long id) {
        this.id = id;
    }

    public long getUpId() {
        return upId;
    }

    public void setUpId(long upId) {
        this.upId = upId;
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

    public String getComment0() {
        return comment0;
    }

    public void setComment0(String comment0) {
        this.comment0 = comment0;
    }

    public String getComment1() {
        return comment1;
    }

    public void setComment1(String comment1) {
        this.comment1 = comment1;
    }

    public String getYear() {
        return year;
    }

    public void setYear(String year) {
        this.year = year;
    }

    public String getBody() {
        return body;
    }

    public void setBody(String body) {
        this.body = body;
    }

    public String getUserName() {
        return userName;
    }

    public void setUserName(String userName) {
        this.userName = userName;
    }

    public String getGroupName() {
        return groupName;
    }

    public void setGroupName(String groupName) {
        this.groupName = groupName;
    }

    public String getPermString() {
        return permString;
    }

    public void setPermString(String permString) {
        this.permString = permString;
    }

    public long[] getGrps() {
        return grps;
    }

    public void setGrps(long[] grps) {
        this.grps = grps;
    }

    public long[] getModbits() {
        return modbits;
    }

    public void setModbits(long[] modbits) {
        this.modbits = modbits;
    }

    public List<Selected<TopicModbit>> getModbitsSelection() {
        return Arrays.stream(TopicModbit.values())
                .map(bit -> new Selected<>(bit, Utils.contains(modbits, bit.getValue())))
                .collect(Collectors.toList());
    }

    public void toTopic(Topic topic, Topic up, User user, User group, RequestContext requestContext) {
        topic.setUp(up.getId() > 0 ? up : null);
        topic.setIdent(!StringUtils.isEmpty(getIdent()) ? getIdent() : null);
        topic.setSubject(Text.convertLigatures(getSubject()));
        topic.setComment0(Text.convertLigatures(getComment0()));
        topic.setComment0Xml(Text.convert(topic.getComment0(), TextFormat.PLAIN, MtextFormat.LINE));
        topic.setComment1(Text.convertLigatures(getComment1()));
        topic.setComment1Xml(Text.convert(topic.getComment1(), TextFormat.PLAIN, MtextFormat.LINE));
        topic.setIndex2(!StringUtils.isEmpty(getYear()) ? Utils.toLong(getYear(), 0L) : 0);
        topic.setBody(Text.convertLigatures(getBody()));
        topic.setBodyXml(Text.convert(topic.getBody(), TextFormat.PLAIN, MtextFormat.SHORT));
        topic.setUser(user);
        topic.setGroup(group);
        topic.setPerms(PermUtils.parse(getPermString()));
        topic.setGrp(Utils.disjunct(getGrps()));
        topic.setModbits(Utils.disjunct(getModbits()));
        topic.setModifier(requestContext.getUser());
        topic.setModified(Utils.now());
        if (getId() <= 0) {
            topic.setSent(Utils.now());
            topic.setCreator(requestContext.getUser());
            topic.setCreated(Utils.now());
        }
    }

    public boolean isTrackChanged(Topic topic) {
        return getId() > 0 && getUpId() != topic.getUpId();
    }

    public boolean isCatalogChanged(Topic topic) {
        return getId() > 0 && (getUpId() != topic.getUpId()
                               || !getIdent().equals(topic.getIdent())
                               || Utils.disjunct(getModbits()) != topic.getModbits());
    }

}