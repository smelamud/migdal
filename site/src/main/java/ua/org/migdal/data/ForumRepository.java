package ua.org.migdal.data;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.querydsl.QuerydslPredicateExecutor;

public interface ForumRepository extends JpaRepository<Forum, Long>, QuerydslPredicateExecutor<Forum> {
}
