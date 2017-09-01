package ua.org.migdal.data;

import java.util.List;

import javax.persistence.DiscriminatorValue;
import javax.persistence.Entity;
import javax.persistence.Transient;

import org.hibernate.Hibernate;
import org.springframework.expression.EvaluationContext;
import org.springframework.expression.spel.support.StandardEvaluationContext;
import org.springframework.util.StringUtils;

import ua.org.migdal.grp.GrpDescriptor;
import ua.org.migdal.grp.GrpEnum;
import ua.org.migdal.session.RequestContext;
import ua.org.migdal.session.RequestContextImpl;

@Entity
@DiscriminatorValue("1")
public class Posting extends Entry {

    @Transient
    private EvaluationContext spelEvaluationContext = new StandardEvaluationContext(this);

    @Transient
    private List<Topic> ancestors;

    private GrpDescriptor getGrpDescriptor() {
        return GrpEnum.getInstance().grp(getGrp()); // FIXME not null-safe
    }

    @Transient
    public String getGrpTitle() {
        return getGrpDescriptor().getTitle();
    }

    private String getGrpHeading() {
        return getGrpDescriptor().getHeading(spelEvaluationContext);
    }

    private String getGrpGeneralHref() {
        return getGrpDescriptor().getGeneralHref(spelEvaluationContext);
    }

    private String getGrpDetailsHref() {
        return getGrpDescriptor().getDetailsHref(spelEvaluationContext);
    }

    @Transient
    public String getHeading() {
        return getHeading(false);
    }

    @Transient
    public String getHeading(boolean useUrl) {
        String heading = getGrpHeading();
        if (StringUtils.isEmpty(heading)) {
            heading = getSubject();
        }
        if (StringUtils.isEmpty(heading)) {
            heading = getBodyTiny();
        }
        if (StringUtils.isEmpty(heading) && useUrl) {
            heading = getGrpDetailsHref();
        }
        if (StringUtils.isEmpty(heading)) {
            heading = "Без названия";
        }
        return heading;
    }

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