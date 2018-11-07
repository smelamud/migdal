package ua.org.migdal.manager;

import java.sql.Timestamp;
import java.util.List;
import javax.inject.Inject;

import org.springframework.stereotype.Service;
import ua.org.migdal.data.ChatMessage;
import ua.org.migdal.data.ChatMessageRepository;

@Service
public class ChatManager {

    @Inject
    private ChatMessageRepository chatMessageRepository;

    public List<ChatMessage> getAll(Timestamp after, Timestamp before) {
        return chatMessageRepository.findBySentAfterAndSentBeforeOrderBySentDesc(after, before);
    }

}
