package ua.org.migdal.data;

import javax.persistence.DiscriminatorValue;
import javax.persistence.Entity;

import org.hibernate.Hibernate;
import ua.org.migdal.session.RequestContext;
import ua.org.migdal.session.RequestContextImpl;
import ua.org.migdal.util.Perm;

@Entity
@DiscriminatorValue("3")
public class Topic extends Entry {

    public Topic() {
    }

    public Topic(Topic up, RequestContext requestContext) {
        setUp(up);
        setGrp(up.getGrp());
        long modbits = up.getModbits();
        modbits = TopicModbit.ROOT.unset(modbits);
        modbits = TopicModbit.TRANSPARENT.unset(modbits);
        setModbits(modbits);
        setUser(requestContext.getUser());
        setGroup(up.getGroup());
        setPerms(up.getPerms());
    }

    @Override
    protected boolean isPermitted(long right) {
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
        if (parent != null || up != null && Hibernate.getClass(up) != Topic.class) {
            return "hierarchy.incorrect";
        }
        return null;
    }

}