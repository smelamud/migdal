package ua.org.migdal.data;

import java.sql.Timestamp;
import javax.persistence.Entity;
import javax.persistence.Id;
import javax.persistence.Table;
import javax.validation.constraints.NotNull;

@Entity
@Table(name="html_cache")
public class HtmlCache {

    @Id
    private String ident = "";

    @NotNull
    private String content = "";

    private Timestamp deadline;

    private Integer postingsVersion;

    private Integer commentsVersion;

    private Integer topicsVersion;

    public String getIdent() {
        return ident;
    }

    public void setIdent(String ident) {
        this.ident = ident;
    }

    public String getContent() {
        return content;
    }

    public void setContent(String content) {
        this.content = content;
    }

    public Timestamp getDeadline() {
        return deadline;
    }

    public void setDeadline(Timestamp deadline) {
        this.deadline = deadline;
    }

    public Integer getPostingsVersion() {
        return postingsVersion;
    }

    public void setPostingsVersion(Integer postingsVersion) {
        this.postingsVersion = postingsVersion;
    }

    public Integer getCommentsVersion() {
        return commentsVersion;
    }

    public void setCommentsVersion(Integer commentsVersion) {
        this.commentsVersion = commentsVersion;
    }

    public Integer getTopicsVersion() {
        return topicsVersion;
    }

    public void setTopicsVersion(Integer topicsVersion) {
        this.topicsVersion = topicsVersion;
    }

}
