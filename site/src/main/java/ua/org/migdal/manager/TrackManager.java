package ua.org.migdal.manager;

import javax.inject.Inject;

import com.querydsl.core.types.Predicate;
import com.querydsl.core.types.dsl.StringPath;
import org.springframework.stereotype.Service;

@Service
public class TrackManager {

    @Inject
    private EntryManager entryManager;

    public Predicate subtree(StringPath trackField, long id) {
        String track = entryManager.getTrackById(id);
        return trackField.like(track != null ? track + '%' : "%");
    }

}