package ua.org.migdal.data;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.querydsl.QueryDslPredicateExecutor;

public interface CrossEntryRepository extends JpaRepository<CrossEntry, Long>, QueryDslPredicateExecutor<CrossEntry> {
}