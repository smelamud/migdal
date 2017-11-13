package ua.org.migdal.data;

import java.sql.Timestamp;
import javax.persistence.Access;
import javax.persistence.AccessType;
import javax.persistence.Entity;
import javax.persistence.Enumerated;
import javax.persistence.FetchType;
import javax.persistence.GeneratedValue;
import javax.persistence.Id;
import javax.persistence.ManyToOne;
import javax.persistence.Table;
import javax.validation.constraints.NotNull;

import ua.org.migdal.util.Utils;

@Entity
@Table(name="votes")
public class Vote {

    @Id
    @GeneratedValue
    @Access(AccessType.PROPERTY)
    private long id;

    @NotNull
    @Enumerated
    private VoteType voteType;

    @ManyToOne(fetch = FetchType.LAZY)
    private Entry entry;

    @NotNull
    private String ip = "";

    @ManyToOne(fetch = FetchType.LAZY)
    private User user;

    @NotNull
    private Timestamp expires = Utils.now();

    @NotNull
    private int vote;

    public long getId() {
        return id;
    }

    public void setId(long id) {
        this.id = id;
    }

    public VoteType getVoteType() {
        return voteType;
    }

    public void setVoteType(VoteType voteType) {
        this.voteType = voteType;
    }

    public Entry getEntry() {
        return entry;
    }

    public void setEntry(Entry entry) {
        this.entry = entry;
    }

    public String getIp() {
        return ip;
    }

    public void setIp(String ip) {
        this.ip = ip;
    }

    public User getUser() {
        return user;
    }

    public void setUser(User user) {
        this.user = user;
    }

    public Timestamp getExpires() {
        return expires;
    }

    public void setExpires(Timestamp expires) {
        this.expires = expires;
    }

    public int getVote() {
        return vote;
    }

    public void setVote(int vote) {
        this.vote = vote;
    }

}