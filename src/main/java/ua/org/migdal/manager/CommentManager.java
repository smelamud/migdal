package ua.org.migdal.manager;

import java.util.function.Consumer;

import javax.inject.Inject;

import org.springframework.data.domain.Page;
import org.springframework.data.domain.PageRequest;
import org.springframework.data.domain.Sort;
import org.springframework.stereotype.Service;

import com.querydsl.core.BooleanBuilder;
import com.querydsl.core.types.Predicate;

import ua.org.migdal.data.Comment;
import ua.org.migdal.data.EntryType;
import ua.org.migdal.data.CommentRepository;
import ua.org.migdal.data.QComment;
import ua.org.migdal.session.RequestContext;
import ua.org.migdal.util.CatalogUtils;
import ua.org.migdal.util.Perm;
import ua.org.migdal.util.TrackUtils;
import ua.org.migdal.util.Utils;

@Service
public class CommentManager implements EntryManagerBase<Comment> {

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
    private HtmlCacheManager htmlCacheManager;

    @Inject
    private CommentRepository commentRepository;

    public Comment get(long id) {
        return commentRepository.findById(id).orElse(null);
    }

    @Override
    public Comment beg(long id) {
        Comment comment = get(id);
        return comment != null && comment.isReadable() ? comment : null;
    }

    @Override
    public void save(Comment comment) {
        if (comment.getId() <= 0) {
            comment.setCreator(requestContext.getUser());
            comment.setCreated(Utils.now());
        }
        comment.setModifier(requestContext.getUser());
        comment.setModified(Utils.now());
        commentRepository.save(comment);
    }

    public void saveAndFlush(Comment comment) {
        if (comment.getId() <= 0) {
            comment.setCreator(requestContext.getUser());
            comment.setCreated(Utils.now());
        }
        comment.setModifier(requestContext.getUser());
        comment.setModified(Utils.now());
        commentRepository.saveAndFlush(comment);
    }

    public void store(
            Comment comment,
            Consumer<Comment> applyChanges,
            boolean newComment,
            boolean trackChanged,
            boolean catalogChanged) {

        String oldTrack = comment.getTrack();
        if (applyChanges != null) {
            applyChanges.accept(comment);
        }
        saveAndFlush(comment); /* We need to have the record in DB to know ID after this point */

        String newTrack = TrackUtils.track(comment.getId(), comment.getUp().getTrack());
        if (newComment) {
            trackManager.setTrackById(comment.getId(), newTrack);
            String newCatalog =
                    CatalogUtils.catalog(EntryType.COMMENT, comment.getId(), "", 0, comment.getUp().getCatalog());
            catalogManager.setCatalogById(comment.getId(), newCatalog);
        }
        if (trackChanged) {
            trackManager.replaceTracks(oldTrack, newTrack);
        }
        if (catalogChanged) {
            catalogManager.updateCatalogs(newTrack);
        }
        postingManager.updateCommentsDetails(comment.getParentId());
        htmlCacheManager.commentsUpdated();
    }

    public void drop(Comment comment) {
        String track = comment.getTrack() + ' ';
        String upTrack = trackManager.get(comment.getUpId()) + ' ';
        long parentId = comment.getParentId();
        commentRepository.updateUpId(comment.getId(), comment.getUpId());
        commentRepository.delete(comment);
        catalogManager.updateCatalogs(track);
        trackManager.replaceTracks(track, upTrack);
        postingManager.updateCommentsDetails(parentId);
        htmlCacheManager.commentsUpdated();
    }

    public Iterable<Comment> begAll(long parentId, int offset, int limit) {
        QComment comment = QComment.comment;
        BooleanBuilder where = new BooleanBuilder();
        where.and(comment.parent.id.eq(parentId));
        where.and(getPermFilter(comment, Perm.READ, false));
        return commentRepository.findAll(where, PageRequest.of(offset / limit, limit, Sort.Direction.ASC, "sent"));
    }

    public long count(long parentId) {
        QComment comment = QComment.comment;
        BooleanBuilder where = new BooleanBuilder();
        where.and(comment.parent.id.eq(parentId));
        where.and(getPermFilter(comment, Perm.READ, true));
        return commentRepository.count(where);
    }

    public Comment begLast(long parentId) {
        QComment comment = QComment.comment;
        BooleanBuilder where = new BooleanBuilder();
        where.and(comment.parent.id.eq(parentId));
        where.and(getPermFilter(comment, Perm.READ, true));
        Page<Comment> page = commentRepository.findAll(where, PageRequest.of(0, 1, Sort.Direction.DESC, "sent"));
        return page.hasContent() ? page.getContent().get(0) : null;
    }

    public int begOffset(long parentId, long id) {
        Comment target = beg(id);
        if (target == null) {
            return -1;
        }

        QComment comment = QComment.comment;
        BooleanBuilder where = new BooleanBuilder();
        where.and(comment.parent.id.eq(parentId));
        where.and(getPermFilter(comment, Perm.READ, false));
        where.and(comment.sent.lt(target.getSent()));
        return (int) commentRepository.count(where);
    }

    /**
     * @return the offset of the page where the given comment is located. If there is no comment id passed or
     *         no such comment exists, the value of {@code offset} parameter is returned.
     */
    public int jumpTo(long postingId, long commentId, int offset, int limit) {
        if (commentId <= 0) {
            return offset;
        }
        int toffset = begOffset(postingId, commentId);
        return toffset >= 0 ? (toffset / limit) * limit : offset;
    }

    private Predicate getPermFilter(QComment comment, long right, boolean asGuest) {
        long eUserId = !asGuest ? requestContext.getUserId() : 0;
        boolean eUserModerator = !asGuest && requestContext.isUserModerator();

        if (eUserModerator) {
            return null;
        }

        BooleanBuilder filter = new BooleanBuilder();
        filter.and(permManager.getFilter(comment.user.id, comment.group.id, comment.perms, right, asGuest));
        if (eUserId <= 0) {
            filter.and(comment.disabled.eq(false));
        } else {
            filter.andAnyOf(
                    comment.disabled.eq(false),
                    comment.user.id.eq(eUserId));
        }
        return filter;
    }

}
