package ua.org.migdal.manager;

import javax.inject.Inject;

import org.springframework.stereotype.Service;

import com.querydsl.core.BooleanBuilder;
import com.querydsl.core.types.Predicate;

import ua.org.migdal.data.Entry;
import ua.org.migdal.data.Posting;
import ua.org.migdal.data.PostingRepository;
import ua.org.migdal.data.QPosting;
import ua.org.migdal.session.RequestContext;

@Service
public class PostingManager implements EntryManagerBase {

    @Inject
    private RequestContext requestContext;

    @Inject
    private PermManager permManager;

    @Inject
    private PostingRepository postingRepository;

    public int getPostingsCount(long topicId) {
        return postingRepository.countByParentId(topicId);
    }

    @Override
    public Posting beg(long id) {
        return null; // TBE
    }

    private Predicate getPermFilter(QPosting posting, long right, boolean asGuest) {
        long eUserId = !asGuest ? requestContext.getUserId() : 0;
        boolean eUserModerator = !asGuest && requestContext.isUserModerator();

        if (eUserModerator) {
            return null;
        }

        BooleanBuilder filter = new BooleanBuilder();
        filter.and(permManager.getFilter(posting.user.id, posting.group.id, posting.perms, right, asGuest));
        if (eUserId <= 0) {
            filter.andNot(posting.disabled);
        } else {
            filter.andAnyOf(
                    posting.disabled.not(),
                    posting.user.id.eq(eUserId));
        }
        return filter;
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