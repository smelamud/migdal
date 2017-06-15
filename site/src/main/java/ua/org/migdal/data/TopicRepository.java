package ua.org.migdal.data;

import java.util.List;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.querydsl.QueryDslPredicateExecutor;

public interface TopicRepository extends JpaRepository<Topic, Long>, QueryDslPredicateExecutor<Topic> {

    List<Topic> findByOrderByTrack();

}