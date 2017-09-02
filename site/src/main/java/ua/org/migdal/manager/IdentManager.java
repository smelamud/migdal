package ua.org.migdal.manager;

import javax.inject.Inject;

import org.springframework.stereotype.Service;
import ua.org.migdal.data.EntryRepository;
import ua.org.migdal.data.IdProjection;
import ua.org.migdal.util.Utils;

@Service
public class IdentManager {

    @Inject
    private EntryRepository entryRepository;

    public long getIdByIdent(String ident) {
        IdProjection idp = entryRepository.findIdByIdent(ident);
        return idp != null ? idp.getId() : 0;
    }

    public long idOrIdent(String ident) {
        return Utils.idOrName(ident, this::getIdByIdent);
    }

}