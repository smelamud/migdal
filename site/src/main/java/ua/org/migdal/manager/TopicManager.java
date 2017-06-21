package ua.org.migdal.manager;

import java.util.List;

import javax.inject.Inject;

import org.springframework.stereotype.Service;

import com.querydsl.core.types.Predicate;

import ua.org.migdal.data.QTopic;
import ua.org.migdal.data.Topic;
import ua.org.migdal.data.TopicRepository;
import ua.org.migdal.session.RequestContext;
import ua.org.migdal.util.Perm;

@Service
public class TopicManager {

    @Inject
    private RequestContext requestContext;

    @Inject
    private TopicRepository entryRepository;

    @Inject
    private PermManager permManager;

    public Topic get(long id) {
        return entryRepository.findOne(id);
    }

    public Topic beg(long id) {
        QTopic topic = QTopic.topic;
        return entryRepository.findOne(topic.id.eq(id).and(getPermFilter(topic, Perm.READ)));
    }

    public void save(Topic topic) {
        entryRepository.save(topic);
    }

    public List<Topic> getAll() {
        return entryRepository.findByOrderByTrack();
    }

    private Predicate getPermFilter(QTopic topic, long right) {
        return getPermFilter(topic, right, false);
    }

    private Predicate getPermFilter(QTopic topic, long right, boolean asGuest) {
        boolean eUserAdminTopics = !asGuest && requestContext.isUserAdminTopics();
        boolean eUserModerator = !asGuest && requestContext.isUserModerator();

        if (eUserAdminTopics && right != Perm.POST) {
            return null;
        }
        if (eUserModerator && right == Perm.POST) {
            return null;
        }
        return permManager.getFilter(topic.user.id, topic.group.id, topic.perms, right, asGuest);
    }

}