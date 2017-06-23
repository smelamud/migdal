package ua.org.migdal.form;

import javax.validation.constraints.Size;

import org.hibernate.validator.constraints.NotBlank;
import ua.org.migdal.data.Topic;

public class TopicForm {

    private long id;

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

    public TopicForm() {
    }

    public TopicForm(Topic topic) {
        if (topic == null) {
            return;
        }

        id = topic.getId();
        ident = topic.getIdent() != null ? topic.getIdent() : "";
        subject = topic.getSubject();
        comment0 = topic.getComment0();
        comment1 = topic.getComment1();
        year = Long.toString(topic.getIndex2());
        body = topic.getBody();
        userName = topic.getUser().getLogin();
        groupName = topic.getGroup().getLogin();
        permString = topic.getPermString();
    }

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

}