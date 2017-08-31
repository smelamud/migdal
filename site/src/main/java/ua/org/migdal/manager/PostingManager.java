package ua.org.migdal.manager;

import java.util.List;

import javax.inject.Inject;

import org.springframework.data.domain.PageRequest;
import org.springframework.data.domain.Sort;
import org.springframework.data.util.Pair;
import org.springframework.stereotype.Service;

import com.querydsl.core.BooleanBuilder;
import com.querydsl.core.types.Predicate;

import ua.org.migdal.data.Entry;
import ua.org.migdal.data.Posting;
import ua.org.migdal.data.PostingRepository;
import ua.org.migdal.data.QPosting;
import ua.org.migdal.session.RequestContext;
import ua.org.migdal.util.Perm;

@Service
public class PostingManager implements EntryManagerBase {

    @Inject
    private RequestContext requestContext;

    @Inject
    private PermManager permManager;

    @Inject
    private TrackManager trackManager;
    
    @Inject
    private PostingRepository postingRepository;

    public int getPostingsCount(long topicId) {
        return postingRepository.countByParentId(topicId);
    }

    @Override
    public Posting beg(long id) {
        return null; // TBE
    }

    public Iterable<Posting> begAll(List<Pair<Long, Boolean>> topicRoots, long[] grps, Long index1) {
        QPosting posting = QPosting.posting;
        return postingRepository.findAll(getWhere(posting, topicRoots, grps, index1),
                new PageRequest(0, 20, Sort.Direction.DESC, "sent"));
    }

    private Predicate getWhere(QPosting posting, List<Pair<Long, Boolean>> topicRoots, long[] grps, Long index1) {
        BooleanBuilder where = new BooleanBuilder();
        if (topicRoots != null) {
            BooleanBuilder byTopic = new BooleanBuilder();
            for (Pair<Long, Boolean> topicRoot : topicRoots) {
                if (topicRoot.getFirst() > 0) {
                    byTopic.or(topicRoot.getSecond()
                            ? trackManager.subtree(posting.track, topicRoot.getFirst())
                            : posting.parent.id.eq(topicRoot.getFirst()));
                }
            }
            where.and(byTopic);
        }
        if (grps != null) {
            BooleanBuilder byGrp = new BooleanBuilder();
            for (long grp : grps) {
                byGrp.or(posting.grp.eq(grp));
            }
            where.and(byGrp);
        }
        if (index1 != null) {
            where.and(posting.index1.eq(index1));
        }
        where.and(getPermFilter(posting, Perm.READ));
        return where;
    }

    private Predicate getPermFilter(QPosting posting, long right) {
        return getPermFilter(posting, right, false);
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
            filter.and(posting.disabled.eq(false));
        } else {
            filter.andAnyOf(
                    posting.disabled.eq(false),
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