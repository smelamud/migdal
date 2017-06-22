package ua.org.migdal.manager;

import java.util.List;

import javax.inject.Inject;

import org.springframework.cache.annotation.Cacheable;
import org.springframework.stereotype.Component;
import ua.org.migdal.data.EntryRepository;

// We need a separate class here, because @Cacheable doesn't work for internal calls
@Component
public class PermManagerInternal {

    @Inject
    private EntryRepository entryRepository;

    @Cacheable("entries-permsall")
    public List<Long> getPermsVariety() {
        return entryRepository.permsVariety();
    }

}