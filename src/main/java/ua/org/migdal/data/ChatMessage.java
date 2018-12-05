package ua.org.migdal.data;

import java.sql.Timestamp;

import javax.persistence.Access;
import javax.persistence.AccessType;
import javax.persistence.Entity;
import javax.persistence.GeneratedValue;
import javax.persistence.Id;
import javax.persistence.ManyToOne;
import javax.persistence.Table;
import javax.persistence.Transient;
import javax.validation.constraints.NotNull;
import javax.validation.constraints.Size;

import ua.org.migdal.mtext.Mtext;
import ua.org.migdal.mtext.MtextFormat;

@Entity
@Table(name="chat_messages")
public class ChatMessage {

    @Id
    @GeneratedValue
    @Access(AccessType.PROPERTY)
    private long id;

    @NotNull
    @Size(max=30)
    private String guestLogin = "";

    @ManyToOne
    private User sender;

    @NotNull
    private Timestamp sent;

    @NotNull
    @Size(max=255)
    private String text = "";

    @NotNull
    @Size(max=255)
    private String textXml = "";

    public long getId() {
        return id;
    }

    public void setId(long id) {
        this.id = id;
    }

    public String getGuestLogin() {
        return guestLogin;
    }

    public void setGuestLogin(String guestLogin) {
        this.guestLogin = guestLogin;
    }

    public User getSender() {
        return sender;
    }

    public void setSender(User sender) {
        this.sender = sender;
    }

    public Timestamp getSent() {
        return sent;
    }

    public void setSent(Timestamp sent) {
        this.sent = sent;
    }

    public String getText() {
        return text;
    }

    public void setText(String text) {
        this.text = text;
    }

    public String getTextXml() {
        return textXml;
    }

    public void setTextXml(String textXml) {
        this.textXml = textXml;
    }

    @Transient
    public Mtext getTextMtext() {
        return new Mtext(getTextXml(), MtextFormat.LINE, getId());
    }

}
