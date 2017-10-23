package ua.org.migdal.manager;

import java.sql.Timestamp;
import java.time.Instant;
import java.time.temporal.ChronoUnit;
import java.util.Collections;
import java.util.List;
import java.util.function.Consumer;

import javax.inject.Inject;

import org.springframework.data.domain.PageRequest;
import org.springframework.data.domain.Sort;
import org.springframework.data.util.Pair;
import org.springframework.stereotype.Service;

import com.querydsl.core.BooleanBuilder;
import com.querydsl.core.types.Predicate;

import ua.org.migdal.Config;
import ua.org.migdal.data.CrossEntry;
import ua.org.migdal.data.EntryType;
import ua.org.migdal.data.LinkType;
import ua.org.migdal.data.Posting;
import ua.org.migdal.data.PostingModbit;
import ua.org.migdal.data.PostingRepository;
import ua.org.migdal.data.QPosting;
import ua.org.migdal.data.Topic;
import ua.org.migdal.data.TopicModbit;
import ua.org.migdal.grp.GrpEnum;
import ua.org.migdal.session.RequestContext;
import ua.org.migdal.util.CatalogUtils;
import ua.org.migdal.util.Perm;
import ua.org.migdal.util.TrackUtils;
import ua.org.migdal.util.Utils;

@Service
public class PostingManager implements EntryManagerBase<Posting> {

    @Inject
    private Config config;

    @Inject
    private GrpEnum grpEnum;

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
    private CrossEntryManager crossEntryManager;

    @Inject
    private PostingRepository postingRepository;

    public int getPostingsCount(long topicId) {
        return postingRepository.countByParentId(topicId);
    }

    public Posting get(long id) {
        return postingRepository.findById(id).orElse(null);
    }

    @Override
    public Posting beg(long id) {
        Posting posting = get(id);
        return posting != null && posting.isReadable() ? posting : null;
    }

    public Posting begLast(long[] grps, long topicId, Long userId) {
        QPosting posting = QPosting.posting;
        List<Pair<Long, Boolean>> topicRoots = topicId <= 0 ? null : Collections.singletonList(Pair.of(topicId, false));
        Iterable<Posting> postings = begAll(topicRoots, grps, null, userId, 0, 1);
        for (Posting lastPosting : postings) {
            return lastPosting;
        }
        return null;
    }

    public Iterable<Posting> begAll(List<Pair<Long, Boolean>> topicRoots, long[] grps, Long index1, Long userId,
                                    int offset, int limit) {
        QPosting posting = QPosting.posting;
        return postingRepository.findAll(getWhere(posting, topicRoots, grps, index1, userId),
                PageRequest.of(offset / limit, limit, Sort.Direction.DESC, "sent"));
    }

    public long countAll(List<Pair<Long, Boolean>> topicRoots, long[] grps, Long index1, Long userId) {
        QPosting posting = QPosting.posting;
        return postingRepository.count(getWhere(posting, topicRoots, grps, index1, userId));
    }

    private Predicate getWhere(QPosting posting, List<Pair<Long, Boolean>> topicRoots, long[] grps, Long index1,
                               Long userId) {
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
        if (userId != null && userId > 0) {
            where.and(posting.user.id.eq(userId));
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
        if (posting.getId() <= 0) {
            posting.setCreator(requestContext.getUser());
            posting.setCreated(Utils.now());
        }
        posting.setModifier(requestContext.getUser());
        posting.setModified(Utils.now());
        postingRepository.save(posting);
    }

    public void saveAndFlush(Posting posting) {
        if (posting.getId() <= 0) {
            posting.setCreator(requestContext.getUser());
            posting.setCreated(Utils.now());
        }
        posting.setModifier(requestContext.getUser());
        posting.setModified(Utils.now());
        postingRepository.saveAndFlush(posting);
    }

    public void delete(Posting posting) {
        postingRepository.delete(posting);
    }

    public void store(
            Posting posting,
            Consumer<Posting> applyChanges,
            boolean newPosting,
            boolean trackChanged,
            boolean catalogChanged,
            boolean topicChanged) {

        if (topicChanged) {
            unpublishPosting(posting);
        }
        String oldTrack = posting.getTrack();
        if (applyChanges != null) {
            applyChanges.accept(posting);
        }
        updateModbits(posting);
        saveAndFlush(posting); /* We need to have the record in DB to know ID after this point */

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
        //answerUpdate($posting->getId());
        if (newPosting || topicChanged) {
            publishPosting(posting);
        }
        /*    if ($original->getId() == 0)
        createCounters($posting->getId(), $posting->getGrp());*/
    }

    private void updateModbits(Posting posting) {
        if (requestContext.isUserModerator()
                || requestContext.getUser() != null && requestContext.getUser().isShames()) {
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

    private void publishPosting(Posting posting) {
        String publishGrp = posting.getGrpPublish();
        if (publishGrp == null) {
            return;
        }
        long grpValue = grpEnum.grpValue(publishGrp);
        if (grpValue <= 0) {
            return;
        }
        long[] grps = new long[] { grpValue };
        Posting publish = begLast(grps, posting.getTopicId(), posting.getUser().getId());
        if (publish == null || publish.getModified().after(
                Timestamp.from(Instant.now().plus(config.getPublishingInterval(), ChronoUnit.HOURS)))) {
            publish = new Posting(grpValue, posting.getTopic(), posting.getTopic(), 0, requestContext);
        }
        publish.setIndex1(publish.getIndex1() + 1);
        store(publish,
                null,
                publish.getId() <= 0,
                false,
                false,
                false);
        crossEntryManager.save(new CrossEntry(publish, LinkType.PUBLISH, posting));
    }

    private void unpublishPosting(Posting posting) {
        String publishGrp = posting.getGrpPublish();
        if (publishGrp == null) {
            return;
        }
        CrossEntry crossEntry = crossEntryManager.get(LinkType.PUBLISH, posting.getId());
        if (crossEntry == null) {
            return;
        }
        Posting publish = (Posting) crossEntry.getSource();
        crossEntryManager.delete(crossEntry);
        if (publish == null) {
            return;
        }
        if (publish.getIndex1() > 1) {
            publish.setIndex1(publish.getIndex1() - 1);
            save(publish);
        } else {
            delete(publish);
        }
    }

}