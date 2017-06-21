package ua.org.migdal.manager;

import java.util.List;

import javax.inject.Inject;
import org.springframework.stereotype.Service;

import ua.org.migdal.data.Topic;
import ua.org.migdal.data.TopicRepository;

@Service
public class TopicManager {

    @Inject
    private TopicRepository entryRepository;

    public Topic get(long id) {
        return entryRepository.findOne(id);
    }

    public void save(Topic topic) {
        entryRepository.save(topic);
    }

    public List<Topic> getAll() {
        return entryRepository.findByOrderByTrack();
    }

}