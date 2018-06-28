package ua.org.migdal.manager;

import java.util.function.Consumer;

import javax.inject.Inject;

import org.springframework.data.domain.Page;
import org.springframework.data.domain.PageRequest;
import org.springframework.data.domain.Sort;
import org.springframework.stereotype.Service;

import com.querydsl.core.BooleanBuilder;
import com.querydsl.core.types.Predicate;

import ua.org.migdal.data.EntryType;
import ua.org.migdal.data.Forum;
import ua.org.migdal.data.ForumRepository;
import ua.org.migdal.data.QForum;
import ua.org.migdal.session.RequestContext;
import ua.org.migdal.util.CatalogUtils;
import ua.org.migdal.util.Perm;
import ua.org.migdal.util.TrackUtils;
import ua.org.migdal.util.Utils;

@Service
public class ForumManager implements EntryManagerBase<Forum> {

    @Inject
    private RequestContext requestContext;

    @Inject
    private PermManager permManager;

    @Inject
    private TrackManager trackManager;

    @Inject
    private CatalogManager catalogManager;

    @Inject
    private PostingManager postingManager;

    @Inject
    private ForumRepository forumRepository;

    public Forum get(long id) {
        return forumRepository.findById(id).orElse(null);
    }

    @Override
    public Forum beg(long id) {
        Forum forum = get(id);
        return forum != null && forum.isReadable() ? forum : null;
    }

    @Override
    public void save(Forum forum) {
        if (forum.getId() <= 0) {
            forum.setCreator(requestContext.getUser());
            forum.setCreated(Utils.now());
        }
        forum.setModifier(requestContext.getUser());
        forum.setModified(Utils.now());
        forumRepository.save(forum);
    }

    public void saveAndFlush(Forum forum) {
        if (forum.getId() <= 0) {
            forum.setCreator(requestContext.getUser());
            forum.setCreated(Utils.now());
        }
        forum.setModifier(requestContext.getUser());
        forum.setModified(Utils.now());
        forumRepository.saveAndFlush(forum);
    }

    public void store(
            Forum forum,
            Consumer<Forum> applyChanges,
            boolean newForum,
            boolean trackChanged,
            boolean catalogChanged) {

        String oldTrack = forum.getTrack();
        if (applyChanges != null) {
            applyChanges.accept(forum);
        }
        saveAndFlush(forum); /* We need to have the record in DB to know ID after this point */

        String newTrack = TrackUtils.track(forum.getId(), forum.getUp().getTrack());
        if (newForum) {
            trackManager.setTrackById(forum.getId(), newTrack);
            String newCatalog = CatalogUtils.catalog(EntryType.FORUM, forum.getId(), "", 0, forum.getUp().getCatalog());
            catalogManager.setCatalogById(forum.getId(), newCatalog);
        }
        if (trackChanged) {
            trackManager.replaceTracks(oldTrack, newTrack);
        }
        if (catalogChanged) {
            catalogManager.updateCatalogs(newTrack);
        }
        postingManager.updateAnswersDetails(forum.getParentId());
    }

    public void drop(Forum forum) {
        String track = forum.getTrack() + ' ';
        String upTrack = trackManager.get(forum.getUpId()) + ' ';
        long parentId = forum.getParentId();
        forumRepository.updateUpId(forum.getId(), forum.getUpId());
        forumRepository.delete(forum);
        catalogManager.updateCatalogs(track);
        trackManager.replaceTracks(track, upTrack);
        postingManager.updateAnswersDetails(parentId);
    }

    public Iterable<Forum> begAll(long parentId, int offset, int limit) {
        QForum forum = QForum.forum;
        BooleanBuilder where = new BooleanBuilder();
        where.and(forum.parent.id.eq(parentId));
        where.and(getPermFilter(forum, Perm.READ, false));
        return forumRepository.findAll(where, PageRequest.of(offset / limit, limit, Sort.Direction.ASC, "sent"));
    }

    public long countAnswers(long parentId) {
        QForum forum = QForum.forum;
        BooleanBuilder where = new BooleanBuilder();
        where.and(forum.parent.id.eq(parentId));
        where.and(getPermFilter(forum, Perm.READ, true));
        return forumRepository.count(where);
    }

    public Forum begLastAnswer(long parentId) {
        QForum forum = QForum.forum;
        BooleanBuilder where = new BooleanBuilder();
        where.and(forum.parent.id.eq(parentId));
        where.and(getPermFilter(forum, Perm.READ, true));
        Page<Forum> page = forumRepository.findAll(where, PageRequest.of(0, 1, Sort.Direction.DESC, "sent"));
        return page.hasContent() ? page.getContent().get(0) : null;
    }

    public int begOffset(long parentId, long id) {
        Forum target = beg(id);
        if (target == null) {
            return -1;
        }

        QForum forum = QForum.forum;
        BooleanBuilder where = new BooleanBuilder();
        where.and(forum.parent.id.eq(parentId));
        where.and(getPermFilter(forum, Perm.READ, false));
        where.and(forum.sent.lt(target.getSent()));
        return (int) forumRepository.count(where);
    }

    private Predicate getPermFilter(QForum forum, long right, boolean asGuest) {
        long eUserId = !asGuest ? requestContext.getUserId() : 0;
        boolean eUserModerator = !asGuest && requestContext.isUserModerator();

        if (eUserModerator) {
            return null;
        }

        BooleanBuilder filter = new BooleanBuilder();
        filter.and(permManager.getFilter(forum.user.id, forum.group.id, forum.perms, right, asGuest));
        if (eUserId <= 0) {
            filter.and(forum.disabled.eq(false));
        } else {
            filter.andAnyOf(
                    forum.disabled.eq(false),
                    forum.user.id.eq(eUserId));
        }
        return filter;
    }

}
