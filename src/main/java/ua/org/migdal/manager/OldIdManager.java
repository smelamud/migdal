package ua.org.migdal.manager;

import javax.inject.Inject;

import org.springframework.stereotype.Service;
import ua.org.migdal.data.OldEntryId;
import ua.org.migdal.data.OldId;
import ua.org.migdal.data.OldIdRepository;

@Service
public class OldIdManager {

    @Inject
    private OldIdRepository oldIdRepository;

    public Long getEntryId(String tableName, long oldId) {
        OldId record = oldIdRepository.findById(new OldEntryId(tableName, oldId)).orElse(null);
        return record != null ? record.getEntry().getId() : null;
    }

}
