package ua.org.migdal.data;

import org.springframework.cache.annotation.CacheEvict;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.querydsl.QueryDslPredicateExecutor;

public interface TopicRepository extends JpaRepository<Topic, Long>, QueryDslPredicateExecutor<Topic> {

    @Override
    @CacheEvict(cacheNames={"entries-permsall", "entries-track"}, allEntries=true)
    <S extends Topic> S save(S s);

}