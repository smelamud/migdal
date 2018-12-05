package ua.org.migdal.manager;

import javax.inject.Inject;

import org.springframework.stereotype.Service;
import ua.org.migdal.data.EntryRepository;
import ua.org.migdal.data.IdProjection;
import ua.org.migdal.session.RequestContext;
import ua.org.migdal.util.CatalogUtils;
import ua.org.migdal.util.Utils;

@Service
public class IdentManager {

    @Inject
    private RequestContext requestContext;

    @Inject
    private EntryRepository entryRepository;

    private long getIdByIdent(String ident) {
        IdProjection idp = entryRepository.findIdByIdent(ident);
        return idp != null ? idp.getId() : 0;
    }

    public long idOrIdent(String ident) {
        return Utils.idOrName(ident, this::getIdByIdent);
    }

    public long topicIdFromRequestPath(int start, int length) {
        String catalog = requestContext.getCatalog(start, length);
        // Historically in migdal/events/ there may be idents containing a number
        // as one of their components. That's why we need to verify this possibility
        // first to be backward compatible.
        if (catalog.startsWith("migdal/events/")) {
            long id = idOrIdent(CatalogUtils.toIdent(catalog));
            if (id > 0) {
                return id;
            }
        }
        return idOrIdent(CatalogUtils.toIdOrIdent(catalog));
    }

    public long postingIdFromRequestPath() {
        String ident = CatalogUtils.toIdOrIdent(requestContext.getCatalog());
        ident = Utils.isNumber(ident) ? ident : "post." + ident;
        return idOrIdent(ident);
    }

    public long postingIdFromRequestPath(int start, int length) {
        String ident = CatalogUtils.toIdOrIdent(requestContext.getCatalog(start, length));
        ident = Utils.isNumber(ident) ? ident : "post." + ident;
        return idOrIdent(ident);
    }

}
