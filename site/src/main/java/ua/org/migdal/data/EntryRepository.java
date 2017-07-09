package ua.org.migdal.data;

import java.util.List;

import org.springframework.cache.annotation.CacheEvict;
import org.springframework.cache.annotation.Cacheable;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Modifying;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.querydsl.QueryDslPredicateExecutor;

public interface EntryRepository extends JpaRepository<Entry, Long>, QueryDslPredicateExecutor<Entry> {

    @Cacheable("entries-permsall")
    @Query("select distinct e.perms from Entry e")
    List<Long> permsVariety();

    @Cacheable("entries-track")
    @Query("select e.track from Entry e where id=?1")
    String findTrackById(long id);

    /*
     * I cannot add this comment everywhere, but... We cannot use key="#id" here because the SpEL expression is
     * calculated in context of proxy method where argument names are absent. So we need to access them by index.
     */
    @CacheEvict(cacheNames="entries-track", key="#a0")
    @Modifying
    @Query("update Entry e set e.track=?2 where id=?1")
    void updateTrackById(long id, String track);

    @CacheEvict(cacheNames="entries-track", allEntries=true)
    @Modifying
    @Query(value="update entries set track=concat(?2, substring(track from ?3)) where track like ?1", nativeQuery=true)
    void replaceTracks(String searchWildcard, String replacement, int tailPosition);

    @Query("select e.modbits from Entry e where id=?1")
    String findModbitsById(long id);

    @Modifying
    @Query("update Entry e set e.modbits=?2 where id=?1")
    void updateModbitsById(long id, long modbits);

}