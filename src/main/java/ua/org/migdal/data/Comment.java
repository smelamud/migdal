package ua.org.migdal.data;

import javax.persistence.DiscriminatorValue;
import javax.persistence.Entity;
import javax.persistence.Transient;

import org.hibernate.Hibernate;
import ua.org.migdal.Config;
import ua.org.migdal.session.RequestContext;
import ua.org.migdal.session.RequestContextImpl;
import ua.org.migdal.util.Utils;

@Entity
@DiscriminatorValue("2")
public class Comment extends Entry {

    public Comment() {
    }

    public Comment(Posting posting, RequestContext requestContext) {
        setParent(posting);
        setUp(posting);
        setUser(requestContext.isLogged() ? requestContext.getUser() : requestContext.getRealUser());
        if (posting != null) {
            setGroup(posting.getGroup());
            setPerms(Config.getInstance().getDefaultCommentPerms());
        }
        setSent(Utils.now());
    }

    @Override
    protected boolean isPermitted(long right) {
        RequestContext rc = RequestContextImpl.getInstance();
        return rc.isUserModerator()
               || (!isDisabled() || getUser().getId() == rc.getUserId()) && super.isPermitted(right);
    }

    @Transient
    public String getQuery() {
        return String.format("?tid=%d#t%d", getId(), getId());
    }

    public static String validateHierarchy(Entry parent, Entry up, long id) {
        String errorCode = Entry.validateHierarchy(parent, up, id);
        if (errorCode != null) {
            return errorCode;
        }
        if (parent == null) {
            return "hierarchyNoParent";
        }
        if (Hibernate.getClass(parent) != Posting.class
                || parent.getId() != up.getId() && Hibernate.getClass(up) != Comment.class) {
            return "hierarchyIncorrect";
        }
        return null;
    }

}