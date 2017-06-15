package ua.org.migdal.data;

import javax.persistence.DiscriminatorValue;
import javax.persistence.Entity;

import ua.org.migdal.session.RequestContext;
import ua.org.migdal.session.RequestContextImpl;

@Entity
@DiscriminatorValue("2")
public class Forum extends Entry {

    @Override
    public boolean isPermitted(long right) {
        RequestContext rc = RequestContextImpl.getInstance();
        return rc.isUserModerator()
               || (!isDisabled() || getUser().getId() == rc.getUserId()) && super.isPermitted(right);
    }

}