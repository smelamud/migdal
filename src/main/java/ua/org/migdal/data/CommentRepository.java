package ua.org.migdal.data;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Modifying;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.querydsl.QuerydslPredicateExecutor;

public interface CommentRepository extends JpaRepository<Comment, Long>, QuerydslPredicateExecutor<Comment> {

    @Modifying
    @Query("update Comment c set c.up.id=?2 where c.up.id=?1")
    void updateUpId(long oldUpId, long newUpId);

}
