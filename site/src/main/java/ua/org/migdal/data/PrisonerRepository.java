package ua.org.migdal.data;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.querydsl.QuerydslPredicateExecutor;

public interface PrisonerRepository extends JpaRepository<Prisoner, Long>, QuerydslPredicateExecutor<Prisoner> {
}
