package ua.org.migdal.data;

import javax.persistence.DiscriminatorValue;
import javax.persistence.Entity;

import ua.org.migdal.session.RequestContext;
import ua.org.migdal.session.RequestContextImpl;
import ua.org.migdal.util.Perm;

@Entity
@DiscriminatorValue("3")
public class Topic extends Entry {

    @Override
    public boolean isPermitted(long right) {
        RequestContext rc = RequestContextImpl.getInstance();
        return right != Perm.POST && rc.isUserAdminTopics()
               || right == Perm.POST && rc.isUserModerator()
               || super.isPermitted(right);
    }

    public static String validateHierarchy(Entry parent, Entry up, long id) {
        String errorCode = Entry.validateHierarchy(parent, up, id);
        if (errorCode != null) {
            return errorCode;
        }
        if (parent != null || up != null && up.getEntryType() != EntryType.TOPIC) {
            return "hierarchy.incorrect";
        }
        return null;
    }

}