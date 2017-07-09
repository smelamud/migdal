package ua.org.migdal.manager;

import javax.inject.Inject;

import org.springframework.stereotype.Service;

import com.querydsl.core.types.Predicate;
import com.querydsl.core.types.dsl.StringPath;

import org.springframework.util.StringUtils;
import ua.org.migdal.data.EntryRepository;

@Service
public class TrackManager {

    @Inject
    private EntryRepository entryRepository;

    static String trackWildcard(String track) {
        return track != null ? track + '%' : "%";
    }

    public Predicate subtree(StringPath trackField, long id) {
        return subtree(trackField, entryRepository.findTrackById(id));
    }

    public Predicate subtree(StringPath trackField, String track) {
        return trackField.like(trackWildcard(track));
    }

    public void setTrack(long id, String track) {
        entryRepository.updateTrackById(id, track);
    }

    public void replaceTracks(String oldTrack, String newTrack) {
        if (StringUtils.isEmpty(oldTrack)) {
            throw new IllegalArgumentException("oldTrack cannot be empty");
        }
        if (StringUtils.isEmpty(newTrack)) {
            throw new IllegalArgumentException("oldTrack cannot be empty");
        }
        if (oldTrack.equals(newTrack)) {
            return;
        }
        entryRepository.replaceTracks(trackWildcard(oldTrack), newTrack, oldTrack.length() + 1);
    }

}