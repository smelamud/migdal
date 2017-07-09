package ua.org.migdal.manager;

import java.util.HashMap;
import java.util.List;
import java.util.Map;
import javax.inject.Inject;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.stereotype.Service;
import ua.org.migdal.data.CatalogBuildProjection;
import ua.org.migdal.data.EntryRepository;
import ua.org.migdal.data.EntryType;
import ua.org.migdal.data.TopicModbit;
import ua.org.migdal.util.CatalogUtils;

@Service
public class CatalogManager {

    private static Logger log = LoggerFactory.getLogger(CatalogManager.class);

    @Inject
    private EntryRepository entryRepository;

    public String getCatalogById(long id) {
        return id > 0 ? entryRepository.findCatalogById(id) : "";
    }

    // FIXME this algoritm is memory-consuming
    public void updateCatalogs(String trackPrefix) {
        List<CatalogBuildProjection> list = entryRepository.findCatalogBuildInfoByTrackLikeOrderByTrack(
                TrackManager.trackWildcard(trackPrefix));
        Map<Long, String> catalogs = new HashMap<>();
        for (CatalogBuildProjection info : list) {
            if (!catalogs.containsKey(info.getUp().getId())) {
                catalogs.put(info.getUp().getId(), getCatalogById(info.getUp().getId()));
            }
            if (info.getEntryType() == EntryType.TOPIC
                    && TopicModbit.ROOT.isSet(info.getModbits()) && TopicModbit.TRANSPARENT.isSet(info.getModbits())) {
                catalogs.put(info.getId(), "");
            } else if (info.getEntryType() == EntryType.TOPIC && TopicModbit.ROOT.isSet(info.getModbits())) {
                catalogs.put(info.getId(), CatalogUtils.catalog(info.getId(), info.getIdent()));
            } else if (info.getEntryType() == EntryType.TOPIC && TopicModbit.TRANSPARENT.isSet(info.getModbits())) {
                catalogs.put(info.getId(), catalogs.get(info.getUp().getId()));
            } else {
                catalogs.put(info.getId(),
                        CatalogUtils.catalog(info.getId(), info.getIdent(), catalogs.get(info.getUp().getId())));
            }
            if (!catalogs.get(info.getId()).equals(info.getCatalog())) {
                entryRepository.updateCatalogById(info.getId(), catalogs.get(info.getId()));
            }
        }
    }

}