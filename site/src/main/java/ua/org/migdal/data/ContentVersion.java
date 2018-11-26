package ua.org.migdal.data;

import javax.persistence.Entity;
import javax.persistence.Id;
import javax.persistence.Table;
import javax.validation.constraints.NotNull;

@Entity
@Table(name="content_versions")
public class ContentVersion {

    @Id
    private long id = 1L; // there is always only one record

    @NotNull
    private int postingsVersion;

    @NotNull
    private int commentsVersion;

    @NotNull
    private int topicsVersion;

    public Long getId() {
        return id;
    }

    public void setId(Long id) {
        this.id = id;
    }

    public int getPostingsVersion() {
        return postingsVersion;
    }

    public void setPostingsVersion(int postingsVersion) {
        this.postingsVersion = postingsVersion;
    }

    public int getCommentsVersion() {
        return commentsVersion;
    }

    public void setCommentsVersion(int commentsVersion) {
        this.commentsVersion = commentsVersion;
    }

    public int getTopicsVersion() {
        return topicsVersion;
    }

    public void setTopicsVersion(int topicsVersion) {
        this.topicsVersion = topicsVersion;
    }

}
