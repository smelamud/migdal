package ua.org.migdal.manager;

import java.util.HashMap;
import java.util.List;
import java.util.Map;

import javax.inject.Inject;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.stereotype.Service;

import ua.org.migdal.data.CatalogBuildProjection;
import ua.org.migdal.data.Entry;
import ua.org.migdal.data.EntryRepository;
import ua.org.migdal.util.CatalogUtils;

@Service
public class CatalogManager {

    private static Logger log = LoggerFactory.getLogger(CatalogManager.class);

    @Inject
    private EntryRepository entryRepository;

    public String getCatalogById(long id) {
        return id > 0 ? entryRepository.findCatalogById(id) : "";
    }

    public void setCatalogById(long id, String catalog) {
        entryRepository.updateCatalogById(id, catalog);
    }

    /**
     * Note that this method does not use {@link Entry#track} and can be used when
     * track information is incorrect (for example, when entries are moved from one
     * subtree to another).
     */
    // FIXME this algoritm is memory-consuming
    public void updateCatalogs(String trackPrefix) {
        List<CatalogBuildProjection> list = entryRepository.findCatalogBuildInfoByTrackLikeOrderByTrack(
                TrackManager.trackWildcard(trackPrefix));
        Map<Long, String> catalogs = new HashMap<>();
        for (CatalogBuildProjection info : list) {
            long upId = info.getUp() != null ? info.getUp().getId() : 0;
            if (upId > 0 && !catalogs.containsKey(upId)) {
                catalogs.put(upId, getCatalogById(upId));
            }
            String upCatalog = upId > 0 ? catalogs.get(upId) : "";
            catalogs.put(
                    info.getId(),
                    CatalogUtils.catalog(info.getEntryType(), info.getId(), info.getIdent(), info.getModbits(),
                                         upCatalog));
            if (!catalogs.get(info.getId()).equals(info.getCatalog())) {
                entryRepository.updateCatalogById(info.getId(), catalogs.get(info.getId()));
            }
        }
    }

}