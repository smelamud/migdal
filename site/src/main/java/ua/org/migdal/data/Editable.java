package ua.org.migdal.data;

import ua.org.migdal.session.RequestContext;

public interface Editable {

    boolean isEditable(RequestContext requestContext);

}
