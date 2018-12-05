package ua.org.migdal.data;

import org.springframework.cache.annotation.CacheEvict;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.querydsl.QuerydslPredicateExecutor;

public interface TopicRepository extends JpaRepository<Topic, Long>, QuerydslPredicateExecutor<Topic> {

    @Override
    @CacheEvict(cacheNames={"entries-permsall", "entries-track", "entries-id"}, allEntries=true)
    <S extends Topic> S save(S s);

    @Query("select count(*) from Topic t where t.up.id=?1")
    int countByUpId(long upId);

}