package ua.org.migdal.manager;

import javax.inject.Inject;

import org.springframework.stereotype.Service;

import com.querydsl.core.types.Predicate;
import com.querydsl.core.types.dsl.StringPath;

import ua.org.migdal.data.EntryRepository;

@Service
public class TrackManager {

    @Inject
    private EntryRepository entryRepository;

    public Predicate subtree(StringPath trackField, long id) {
        String track = entryRepository.findTrackById(id);
        return trackField.like(track != null ? track + '%' : "%");
    }

}