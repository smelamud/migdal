package ua.org.migdal.data;

import org.springframework.cache.annotation.CacheEvict;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.querydsl.QuerydslPredicateExecutor;

public interface PostingRepository extends JpaRepository<Posting, Long>, QuerydslPredicateExecutor<Posting> {

    @Override
    @CacheEvict(cacheNames={"entries-permsall", "entries-track", "entries-id"}, allEntries=true)
    <S extends Posting> S save(S s);

    @Query("select count(*) from Posting p where p.parent.id=?1")
    int countByParentId(long upId);

}