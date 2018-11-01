package ua.org.migdal.data;

import javax.persistence.DiscriminatorValue;
import javax.persistence.Entity;
import javax.persistence.Transient;

import org.hibernate.Hibernate;
import ua.org.migdal.grp.GrpEnum;
import ua.org.migdal.session.RequestContext;
import ua.org.migdal.session.RequestContextImpl;
import ua.org.migdal.util.Perm;

@Entity
@DiscriminatorValue("3")
public class Topic extends Entry {

    public Topic() {
    }

    public Topic(Topic up, RequestContext requestContext) {
        if (up != null && up.getId() > 0) { // Virtual root topic with id == 0 may be passed
            setUp(up);
        }
        setGrp(up.getGrp());
        long modbits = up.getModbits();
        modbits = TopicModbit.ROOT.unset(modbits);
        modbits = TopicModbit.TRANSPARENT.unset(modbits);
        setModbits(modbits);
        setUser(requestContext.getUser());
        setGroup(up.getGroup());
        setPerms(up.getPerms());
    }

    @Transient
    public String getHeading() {
        return getSubject();
    }

    @Transient
    public String getYearName() {
        return String.format("%d/%d-%d г.", getIndex2(), getIndex2() - 3761, getIndex2() - 3760);
    }

    public boolean accepts(long grp) {
        return (getGrp() & grp) != 0;
    }

    public boolean accepts(String grpName) {
        return accepts(GrpEnum.getInstance().grpValue(grpName));
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
            return "hierarchyIncorrect";
        }
        return null;
    }

}