package ua.org.migdal.manager;

import javax.inject.Inject;

import org.springframework.stereotype.Service;

import ua.org.migdal.data.Entry;
import ua.org.migdal.data.Posting;
import ua.org.migdal.data.PostingRepository;

@Service
public class PostingManager implements EntryManagerBase {

    @Inject
    private PostingRepository postingRepository;

    public int getPostingsCount(long topicId) {
        return postingRepository.countByParentId(topicId);
    }

    @Override
    public Posting beg(long id) {
        return null; // TBE
    }

    @Override
    public void save(Entry entry) {
        if (!(entry instanceof Posting)) {
            throw new IllegalArgumentException("PostingManager accepts Posting entries only");
        }
        save((Posting) entry);
    }

    public void save(Posting posting) {
        postingRepository.save(posting);
    }

}