package ua.org.migdal.data;

import javax.persistence.DiscriminatorValue;
import javax.persistence.Entity;

import org.hibernate.Hibernate;
import ua.org.migdal.Config;
import ua.org.migdal.session.RequestContext;
import ua.org.migdal.session.RequestContextImpl;
import ua.org.migdal.util.Utils;

@Entity
@DiscriminatorValue("2")
public class Forum extends Entry {

    public Forum() {
    }

    public Forum(Posting posting, RequestContext requestContext) {
        setParent(posting);
        setUp(posting);
        setUser(requestContext.isLogged() ? requestContext.getUser() : requestContext.getRealUser());
        if (posting != null) {
            setGroup(posting.getGroup());
            setPerms(Config.getInstance().getDefaultForumPerms());
        }
        setSent(Utils.now());
    }

    @Override
    protected boolean isPermitted(long right) {
        RequestContext rc = RequestContextImpl.getInstance();
        return rc.isUserModerator()
               || (!isDisabled() || getUser().getId() == rc.getUserId()) && super.isPermitted(right);
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
                || parent.getId() != up.getId() && Hibernate.getClass(up) != Forum.class) {
            return "hierarchyIncorrect";
        }
        return null;
    }

}