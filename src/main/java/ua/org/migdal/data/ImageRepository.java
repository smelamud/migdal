package ua.org.migdal.data;

import java.sql.Timestamp;

import org.springframework.cache.annotation.CacheEvict;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Modifying;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.querydsl.QuerydslPredicateExecutor;

public interface ImageRepository extends JpaRepository<Image, Long>, QuerydslPredicateExecutor<Image> {

    @Override
    @CacheEvict(cacheNames={"entries-permsall", "entries-track", "entries-id"}, allEntries=true)
    <S extends Image> S save(S s);

    @Modifying
    @Query("delete from Image im"
            + " where not exists (select ii.id from InnerImage ii where ii.image.id = im.id)"
            + " and im.created < ?1")
    void deleteObsolete(Timestamp deadline);

}