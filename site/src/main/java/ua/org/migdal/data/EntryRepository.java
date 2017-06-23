package ua.org.migdal.data;

import java.util.List;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Modifying;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.querydsl.QueryDslPredicateExecutor;

public interface EntryRepository extends JpaRepository<Entry, Long>, QueryDslPredicateExecutor<Entry> {

    @Query("select distinct e.perms from Entry e")
    List<Long> permsVariety();

    @Query("select e.track from Entry e where id=?1")
    String findTrackById(long id);

    @Query("select e.modbits from Entry e where id=?1")
    String findModbitsById(long id);

    @Modifying
    @Query("update Entry e set e.modbits=?2 where id=?1")
    String updateModbitsById(long id);

}