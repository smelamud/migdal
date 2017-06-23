package ua.org.migdal.manager;

import java.util.List;

import javax.inject.Inject;

import org.springframework.cache.annotation.Cacheable;
import org.springframework.data.jpa.repository.Modifying;
import org.springframework.data.jpa.repository.Query;
import org.springframework.stereotype.Component;

import ua.org.migdal.data.EntryRepository;

// We need a separate class here, because @Cacheable doesn't work for internal calls
@Component
public class EntryManager {

    @Inject
    private EntryRepository entryRepository;

    @Cacheable("entries-permsall")
    public List<Long> getPermsVariety() {
        return entryRepository.permsVariety();
    }

    @Cacheable("entries-track")
    public String getTrackById(long id) {
        return entryRepository.findTrackById(id);
    }

    @Query("select e.modbits from Entry e where id=?1")
    public String findModbitsById(long id) {
        return entryRepository.findModbitsById(id);
    }

    @Modifying
    @Query("update Entry e set e.modbits=?2 where id=?1")
    public String updateModbitsById(long id) {
        return entryRepository.updateModbitsById(id);
    }

}