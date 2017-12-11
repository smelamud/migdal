package ua.org.migdal.manager;

import javax.inject.Inject;

import org.springframework.data.domain.PageRequest;
import org.springframework.data.domain.Sort;
import org.springframework.stereotype.Service;

import com.querydsl.core.BooleanBuilder;
import com.querydsl.core.types.Predicate;

import ua.org.migdal.data.Forum;
import ua.org.migdal.data.ForumRepository;
import ua.org.migdal.data.QForum;
import ua.org.migdal.session.RequestContext;
import ua.org.migdal.util.Perm;

@Service
public class ForumManager {

    @Inject
    private RequestContext requestContext;

    @Inject
    private PermManager permManager;

    @Inject
    private ForumRepository forumRepository;

    public Iterable<Forum> begAll(long parentId, int offset, int limit) {
        QForum forum = QForum.forum;
        BooleanBuilder where = new BooleanBuilder();
        where.and(forum.parent.id.eq(parentId));
        where.and(getPermFilter(forum, Perm.READ, false));
        return forumRepository.findAll(where, PageRequest.of(offset / limit, limit, Sort.Direction.DESC, "sent"));
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
