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
        return idOrIdent(CatalogUtils.toIdent(requestContext.getCatalog(start, length)));
    }

    public long postingIdFromRequestPath(int start, int length) {
        String ident = CatalogUtils.toIdent(requestContext.getCatalog(start, length));
        ident = Utils.isNumber(ident) ? ident : "post." + ident;
        return idOrIdent(ident);
    }

}
