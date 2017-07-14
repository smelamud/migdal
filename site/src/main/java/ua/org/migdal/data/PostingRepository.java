package ua.org.migdal.data;

import org.springframework.cache.annotation.CacheEvict;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.querydsl.QueryDslPredicateExecutor;

public interface PostingRepository extends JpaRepository<Topic, Long>, QueryDslPredicateExecutor<Posting> {

    @Override
    @CacheEvict(cacheNames={"entries-permsall", "entries-track"}, allEntries=true)
    <S extends Topic> S save(S s);

    @Query("select count(*) from Posting p where p.parent.id=?1")
    int countByParentId(long upId);

}