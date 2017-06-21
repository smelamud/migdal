package ua.org.migdal.data;

import java.util.List;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.querydsl.QueryDslPredicateExecutor;

public interface EntryRepository extends JpaRepository<Entry, Long>, QueryDslPredicateExecutor<Entry> {

    @Query("select distinct e.perms from Entry e")
    List<Long> permsVariety();

}