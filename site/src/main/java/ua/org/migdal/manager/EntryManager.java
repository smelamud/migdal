package ua.org.migdal.manager;

import javax.inject.Inject;

import org.springframework.stereotype.Service;

import ua.org.migdal.data.EntryRepository;

@Service
public class EntryManager {

    @Inject
    private EntryRepository entryRepository;

    public boolean exists(long id) {
        return entryRepository.existsById(id);
    }

    public void updateDisabledById(long id, boolean disabled) {
        entryRepository.updateDisabledById(id, disabled);
    }

}