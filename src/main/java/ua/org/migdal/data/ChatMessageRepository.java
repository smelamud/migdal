package ua.org.migdal.data;

import java.sql.Timestamp;
import java.util.List;

import org.springframework.data.jpa.repository.JpaRepository;

public interface ChatMessageRepository extends JpaRepository<ChatMessage, Long> {

    List<ChatMessage> findBySentAfterAndSentBeforeOrderBySentDesc(Timestamp after, Timestamp before);

}
