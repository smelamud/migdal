package ua.org.migdal.data;

import org.springframework.cache.annotation.CacheEvict;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.querydsl.QuerydslPredicateExecutor;

public interface ImageRepository extends JpaRepository<Image, Long>, QuerydslPredicateExecutor<Image> {

    @Override
    @CacheEvict(cacheNames={"entries-permsall", "entries-track", "entries-id"}, allEntries=true)
    <S extends Image> S save(S s);

}