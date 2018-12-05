package ua.org.migdal.manager;

import javax.inject.Inject;

import org.springframework.stereotype.Service;

import ua.org.migdal.data.Entry;
import ua.org.migdal.data.EntryRepository;
import ua.org.migdal.util.Utils;

@Service
public class EntryManager {

    @Inject
    private EntryRepository entryRepository;

    public Entry get(long id) {
        return entryRepository.getOne(id);
    }

    public void save(Entry entity) {
        entryRepository.save(entity);
    }

    public boolean exists(long id) {
        return entryRepository.existsById(id);
    }

    public void updateDisabledById(long id, boolean disabled) {
        entryRepository.updateDisabledById(id, disabled);
    }

    public void renewById(long id) {
        entryRepository.updateSentById(id, Utils.now());
    }

}