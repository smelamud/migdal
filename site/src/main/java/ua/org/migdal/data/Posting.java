package ua.org.migdal.data;

import java.util.List;
import javax.persistence.DiscriminatorValue;
import javax.persistence.Entity;
import javax.persistence.Transient;

import org.hibernate.Hibernate;
import ua.org.migdal.session.RequestContext;
import ua.org.migdal.session.RequestContextImpl;

@Entity
@DiscriminatorValue("1")
public class Posting extends Entry {

    @Transient
    private List<Topic> ancestors;

    public List<Topic> getAncestors() {
        return ancestors;
    }

    public void setAncestors(List<Topic> ancestors) {
        this.ancestors = ancestors;
    }

    @Override
    public boolean isPermitted(long right) {
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
            return "hierarchy.noParent";
        }
        if (Hibernate.getClass(parent) != Topic.class
                || parent.getId() != up.getId() && Hibernate.getClass(up) != Posting.class) {
            return "hierarchy.incorrect";
        }
        return null;
    }

}