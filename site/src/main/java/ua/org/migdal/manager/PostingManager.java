package ua.org.migdal.manager;

import java.sql.Timestamp;
import java.time.Instant;
import java.time.temporal.ChronoUnit;
import java.util.ArrayList;
import java.util.HashSet;
import java.util.Iterator;
import java.util.List;
import java.util.Random;
import java.util.Set;
import java.util.function.Consumer;

import javax.inject.Inject;
import javax.transaction.Transactional;

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
import ua.org.migdal.data.User;
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
    private ForumManager forumManager;

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
    private HtmlCacheManager htmlCacheManager;

    @Inject
    private PostingRepository postingRepository;

    public int getPostingsCount(long topicId) {
        return postingRepository.countByParentId(topicId);
    }

    public boolean exists(long id) {
        return postingRepository.existsById(id);
    }

    public Posting get(long id) {
        return postingRepository.findById(id).orElse(null);
    }

    @Override
    public Posting beg(long id) {
        Posting posting = get(id);
        return posting != null && posting.isReadable() ? posting : null;
    }

    public Posting begFirst(Postings p) {
        Iterator<Posting> postingsIterator = begAll(p.page(0, 1)).iterator();
        return postingsIterator.hasNext() ? postingsIterator.next() : null;
    }

    public Posting begFirstByIndex0(long upId) {
        return begNextByIndex0(upId, -1, true);
    }

    public Posting begNextByIndex0(long upId, long index0, boolean afterIndex0) {
        return begFirst(Postings.all()
                                .up(upId)
                                .index0(index0, afterIndex0)
                                .sort(afterIndex0, "index0"));
    }

    public Posting begNextByIndex1(long upId, String grpName, long index1, boolean afterIndex1) {
        return begFirst(Postings.all()
                .up(upId)
                .grp(grpName)
                .index1(index1, afterIndex1)
                .sort(afterIndex1, "index1"));
    }

    public Iterable<Posting> begLastDiscussions(long[] grps, long[] additionalGrps, boolean prioritize,
                                                int offset, int limit) {
        QPosting posting = QPosting.posting;
        BooleanBuilder where = new BooleanBuilder();
        where.or(getWhere(posting, Postings.all().grp(grps).asGuest().withAnswers()));
        where.or(getWhere(posting, Postings.all().grp(additionalGrps).asGuest()));
        Sort sort;
        if (prioritize) {
            sort = Sort.by(
                       Sort.Order.asc("priority"),
                       Sort.Order.desc("lastAnswerTimestamp")
                   );
        } else {
            sort = Sort.by(Sort.Direction.DESC, "lastAnswerTimestamp");
        }
        return postingRepository.findAll(where, PageRequest.of(offset / limit, limit, sort));
    }

    public Iterable<Posting> begAll(Postings p) {
        return postingRepository.findAll(getWhere(QPosting.posting, p),
                PageRequest.of(p.offset / p.limit, p.limit, p.sortDirection, p.sortFields));
    }

    public List<Posting> begAllAsList(Postings p) {
        List<Posting> list = new ArrayList<>();
        begAll(p).forEach(list::add);
        return list;
    }

    public long countAll(Postings p) {
        return postingRepository.count(getWhere(QPosting.posting, p));
    }

    // TODO ineffective
    public Set<Posting> begRandomWithPriorities(Postings p) {
        Iterable<Posting> postings = postingRepository.findAll(
                getWhere(QPosting.posting, p),
                Sort.by(p.sortDirection, p.sortFields));

        List<Posting> postingList = new ArrayList<>();
        for (Posting post : postings) {
            for (int i = 0; i < 1 - post.getPriority(); i++) {
                postingList.add(post);
            }
        }

        Set<Posting> selected = new HashSet<>();
        Random random = new Random();
        while (selected.size() < p.limit && selected.size() < postingList.size()) {
            selected.add(postingList.get(random.nextInt(postingList.size())));
        }

        return selected;
    }

    public Set<Posting> begRandom(Postings p) {
        Predicate where = getWhere(QPosting.posting, p);
        int size = (int) postingRepository.count(where);

        Set<Posting> selected = new HashSet<>();
        Random random = new Random();
        while (selected.size() < p.limit && selected.size() < size) {
            postingRepository.findAll(where, PageRequest.of(random.nextInt(size), 1, Sort.Direction.DESC, "sent"))
                    .forEach(selected::add);
        }

        return selected;
    }

    public Posting begRandomOne(Postings p) {
        Set<Posting> postings = begRandom(p.limit(1));
        return !postings.isEmpty() ? postings.iterator().next() : null;
    }

    public List<User> getOwners(long topicId) {
        return postingRepository.findOwnersByParentId(topicId);
    }

    private Predicate getWhere(QPosting posting, Postings p) {
        BooleanBuilder where = new BooleanBuilder();
        if (p.topicRoots != null) {
            BooleanBuilder byTopic = new BooleanBuilder();
            for (Pair<Long, Boolean> topicRoot : p.topicRoots) {
                if (topicRoot.getFirst() > 0) {
                    byTopic.or(topicRoot.getSecond()
                            ? trackManager.subtree(posting.track, topicRoot.getFirst())
                            : posting.parent.id.eq(topicRoot.getFirst()));
                }
            }
            where.and(byTopic);
        }
        if (p.grps != null) {
            BooleanBuilder byGrp = new BooleanBuilder();
            for (long grp : p.grps) {
                byGrp.or(posting.grp.eq(grp));
            }
            where.and(byGrp);
        }
        if (p.upId != null) {
            where.and(posting.up.id.eq(p.upId));
        }
        if (p.index0 != null) {
            where.and(p.afterIndex0 ? posting.index0.gt(p.index0) : posting.index0.lt(p.index0));
        }
        if (p.index1 != null) {
            if (p.atIndex1) {
                where.and(posting.index1.eq(p.index1));
            } else {
                where.and(p.afterIndex1 ? posting.index1.gt(p.index1) : posting.index1.lt(p.index1));
            }
        }
        if (p.userId != null && p.userId > 0) {
            where.and(posting.user.id.eq(p.userId));
        }
        if (p.modbit != null) {
            where.and(getModbitFilter(posting, p.modbit));
        }
        if (p.earlierThan != null) {
            where.and(posting.sent.before(p.earlierThan));
        }
        if (p.laterThan != null) {
            where.and(posting.sent.after(p.laterThan));
        }
        if (p.withAnswers) {
            where.and(posting.answers.gt(0));
        }
        where.and(getPermFilter(posting, Perm.READ, p.asGuest));
        return where;
    }

    private Predicate getModbitFilter(QPosting posting, PostingModbit modbit) {
        if (modbit == PostingModbit.HIDDEN) {
            return permManager.getReverseMask(posting.perms, Perm.OR | Perm.ER);
        }
        if (modbit == PostingModbit.DISABLED) {
            return posting.disabled.eq(true);
        }

        BooleanBuilder builder = new BooleanBuilder();
        if (!modbit.isSpecial()) {
            for (Long modbits : postingRepository.modbitsVariety()) {
                if ((modbits & modbit.getValue()) != 0) {
                    builder.or(posting.modbits.eq(modbits));
                }
            }
        }
        return builder;
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
        if (!newPosting) {
            posting.setAnswers(forumManager.countAnswers(posting.getId()));
            posting.setLastAnswerDetails(forumManager.begLastAnswer(posting.getId()));
        } else {
            posting.setLastAnswerDetails(null);
        }
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
        htmlCacheManager.postingsUpdated();
        if (newPosting || topicChanged) {
            publishPosting(posting);
        }
        /*    if ($original->getId() == 0) TODO
        createCounters($posting->getId(), $posting->getGrp());*/
    }

    public void updateModbits(Posting posting) {
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

    public void drop(Posting posting) {
        unpublishPosting(posting);
        String track = posting.getTrack() + ' ';
        String upTrack = trackManager.get(posting.getUpId()) + ' ';
        postingRepository.updateUpId(posting.getId(), posting.getUpId());
        postingRepository.delete(posting);
        catalogManager.updateCatalogs(track);
        trackManager.replaceTracks(track, upTrack);
        htmlCacheManager.postingsUpdated();
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
        Posting publish = begFirst(Postings.all().grp(grps).topic(posting.getTopicId()).user(posting.getUser().getId()));
        if (publish == null || publish.getModified().before(
                Timestamp.from(Instant.now().minus(config.getPublishingInterval(), ChronoUnit.HOURS)))) {
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
        Posting publish = get(crossEntry.getSource().getId());
        crossEntryManager.delete(crossEntry);
        if (publish == null) {
            return;
        }
        if (publish.getIndex1() > 1) {
            publish.setIndex1(publish.getIndex1() - 1);
            save(publish);
        } else {
            postingRepository.delete(publish);
        }
    }

    @Transactional
    public void updateAnswersDetails(long postingId) {
        Posting posting = get(postingId);
        if (posting == null) {
            return;
        }
        posting.setAnswers(forumManager.countAnswers(postingId));
        posting.setLastAnswerDetails(forumManager.begLastAnswer(postingId));
        saveAndFlush(posting);
        htmlCacheManager.forumsUpdated();
    }

}