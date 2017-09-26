package ua.org.migdal.manager;

import java.util.List;
import java.util.function.Consumer;

import javax.inject.Inject;

import org.springframework.data.domain.PageRequest;
import org.springframework.data.domain.Sort;
import org.springframework.data.util.Pair;
import org.springframework.stereotype.Service;

import com.querydsl.core.BooleanBuilder;
import com.querydsl.core.types.Predicate;

import ua.org.migdal.data.EntryType;
import ua.org.migdal.data.Posting;
import ua.org.migdal.data.PostingModbit;
import ua.org.migdal.data.PostingRepository;
import ua.org.migdal.data.QPosting;
import ua.org.migdal.data.Topic;
import ua.org.migdal.data.TopicModbit;
import ua.org.migdal.session.RequestContext;
import ua.org.migdal.util.CatalogUtils;
import ua.org.migdal.util.Perm;
import ua.org.migdal.util.TrackUtils;

@Service
public class PostingManager implements EntryManagerBase<Posting> {

    @Inject
    private RequestContext requestContext;

    @Inject
    private TopicManager topicManager;

    @Inject
    private PermManager permManager;

    @Inject
    private TrackManager trackManager;

    @Inject
    private CatalogManager catalogManager;

    @Inject
    private SpamManager spamManager;

    @Inject
    private PostingRepository postingRepository;

    public int getPostingsCount(long topicId) {
        return postingRepository.countByParentId(topicId);
    }

    public Posting get(long id) {
        return postingRepository.findOne(id);
    }

    @Override
    public Posting beg(long id) {
        Posting posting = get(id);
        return posting != null && posting.isReadable() ? posting : null;
    }

    public Iterable<Posting> begAll(List<Pair<Long, Boolean>> topicRoots, long[] grps, Long index1,
                                    int offset, int limit) {
        QPosting posting = QPosting.posting;
        return postingRepository.findAll(getWhere(posting, topicRoots, grps, index1),
                new PageRequest(offset / limit, limit, Sort.Direction.DESC, "sent"));
    }

    public long countAll(List<Pair<Long, Boolean>> topicRoots, long[] grps, Long index1) {
        QPosting posting = QPosting.posting;
        return postingRepository.count(getWhere(posting, topicRoots, grps, index1));
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
    public void save(Posting posting) {
        postingRepository.save(posting);
    }

    public void saveAndFlush(Posting posting) {
        postingRepository.saveAndFlush(posting);
    }

    public void store(
            Posting posting,
            Consumer<Posting> applyChanges,
            boolean newPosting,
            boolean trackChanged,
            boolean catalogChanged) {

        /*$topicChanged = !is_null($original) && $original->getId() != 0
                && $posting->getTopicId() != $original->getTopicId(); DO THIS IN CONTROLLER?

        if ($topicChanged)
            unpublishPosting($original);*/
        String oldTrack = posting.getTrack();
        applyChanges.accept(posting);
        updateModbits(posting);
        saveAndFlush(posting); /* We need to have the record in DB and to know ID
                                                             after this point */

        String newTrack = TrackUtils.track(posting.getId(), posting.getUp().getTrack());
        if (newPosting) {
            trackManager.setTrackById(posting.getId(), newTrack);
            String newCatalog = CatalogUtils.catalog(EntryType.POSTING, posting.getId(), posting.getIdent(),
                    posting.getModbits(), posting.getUp().getCatalog());
            catalogManager.setCatalogById(posting.getId(), newCatalog);
        }
        if (trackChanged) {
            trackManager.replaceTracks(oldTrack, newTrack);
        }
        if (catalogChanged) {
            catalogManager.updateCatalogs(newTrack);
        }
        /*answerUpdate($posting->getId());
        if ($topicChanged)
            publishPosting($posting);*/
        /*    if ($original->getId() == 0)
        createCounters($posting->getId(), $posting->getGrp());*/
    }

    private void updateModbits(Posting posting) {
        if (requestContext.isUserModerator() || requestContext.getUser().isShames()) {
            return;
        }

        Topic topic = posting.getTopic() != null ? posting.getTopic() : topicManager.rootTopic();
        boolean attention = spamManager.needsAttention(posting);
        if ((topic.hasModbit(TopicModbit.PREMODERATE) || attention) && posting.getId() <= 0) {
            posting.setDisabled(true);
        }
        if (topic.hasModbit(TopicModbit.MODERATE)) {
            posting.setModbit(PostingModbit.MODERATE);
        }
        if (topic.hasModbit(TopicModbit.EDIT)) {
            posting.setModbit(PostingModbit.EDIT);
        }
        if (attention) {
            posting.setModbit(PostingModbit.ATTENTION);
        }
    }

}