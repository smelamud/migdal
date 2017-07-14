package ua.org.migdal.manager;

import javax.inject.Inject;

import org.springframework.stereotype.Service;
import ua.org.migdal.data.PostingRepository;

@Service
public class PostingManager {

    @Inject
    private PostingRepository postingRepository;

    public int getPostingsCount(long topicId) {
        return postingRepository.countByParentId(topicId);
    }

}