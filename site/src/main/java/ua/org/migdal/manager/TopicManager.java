package ua.org.migdal.manager;

import java.util.ArrayList;
import java.util.LinkedList;
import java.util.List;
import java.util.function.Consumer;

import javax.inject.Inject;

import org.springframework.data.domain.Sort;
import org.springframework.stereotype.Service;

import com.querydsl.core.BooleanBuilder;
import com.querydsl.core.types.Predicate;

import ua.org.migdal.Config;
import ua.org.migdal.data.Entry;
import ua.org.migdal.data.EntryRepository;
import ua.org.migdal.data.EntryType;
import ua.org.migdal.data.IdNameProjection;
import ua.org.migdal.data.QTopic;
import ua.org.migdal.data.Topic;
import ua.org.migdal.data.TopicRepository;
import ua.org.migdal.data.util.Tree;
import ua.org.migdal.data.util.TreeNode;
import ua.org.migdal.grp.GrpEnum;
import ua.org.migdal.session.RequestContext;
import ua.org.migdal.util.CatalogUtils;
import ua.org.migdal.util.Perm;
import ua.org.migdal.util.TrackUtils;

@Service
public class TopicManager implements EntryManagerBase<Topic> {

    @Inject
    private Config config;

    @Inject
    private RequestContext requestContext;

    @Inject
    private EntryRepository entryRepository;

    @Inject
    private TopicRepository topicRepository;

    @Inject
    private PermManager permManager;

    @Inject
    private TrackManager trackManager;

    @Inject
    private CatalogManager catalogManager;

    @Inject
    private UserManager userManager;

    @Inject
    private GrpEnum grpEnum;

    public Topic rootTopic() {
        Topic topic = new Topic();
        topic.setGrp(grpEnum.all);
        topic.setModbits(config.getRootTopicModbits());
        topic.setUser(userManager.getByLogin(config.getRootTopicUserName()));
        topic.setGroup(userManager.getByLogin(config.getRootTopicGroupName()));
        topic.setPerms(config.getRootTopicPerms());
        return topic;
    }

    public Topic get(long id) {
        return topicRepository.findById(id).orElse(null);
    }

    @Override
    public Topic beg(long id) {
        Topic topic = get(id);
        return topic != null && topic.isReadable() ? topic : null;
    }

    public Topic begOrRoot(long id) {
        return id > 0 ? beg(id) : rootTopic();
    }

    @Override
    public void save(Topic topic) {
        topicRepository.save(topic);
    }

    public void saveAndFlush(Topic topic) {
        topicRepository.saveAndFlush(topic);
    }

    public void store(
            Topic topic,
            Consumer<Topic> applyChanges,
            boolean newTopic,
            boolean trackChanged,
            boolean catalogChanged) {

        String oldTrack = topic.getTrack();
        applyChanges.accept(topic);
        saveAndFlush(topic); // We need to have the record in DB and to know ID after this point

        Entry up = topic.getUp() != null ? topic.getUp() : rootTopic();
        String newTrack = TrackUtils.track(topic.getId(), up.getTrack());
        if (newTopic) {
            trackManager.setTrackById(topic.getId(), newTrack);
            String newCatalog = CatalogUtils.catalog(EntryType.TOPIC, topic.getId(), topic.getIdent(),
                    topic.getModbits(), up.getCatalog());
            catalogManager.setCatalogById(topic.getId(), newCatalog);
        }
        if (trackChanged) {
            trackManager.replaceTracks(oldTrack, newTrack);
        }
        if (catalogChanged) {
            catalogManager.updateCatalogs(newTrack);
        }
    }

    public Iterable<Topic> begAll() {
        return begAll(0, true);
    }

    public Iterable<Topic> begAll(long upId, boolean recursive) {
        return begAll(upId, recursive, "subject");
    }

    public Iterable<Topic> begAll(long upId, boolean recursive, String sortField) {
        QTopic topic = QTopic.topic;
        return topicRepository.findAll(getWhere(topic, upId, recursive), new Sort(Sort.Direction.ASC, sortField));
    }

    private Predicate getWhere(QTopic topic, long upId, boolean recursive) {
        BooleanBuilder where = new BooleanBuilder();
        if (upId > 0) {
            where.and(recursive ? trackManager.subtree(topic.track, upId) : topic.up.id.eq(upId));
        }
        where.and(getPermFilter(topic, Perm.READ));
        return where;
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

    public List<Topic> begAncestors(long id) {
        LinkedList<Topic> ancestors = new LinkedList<>();
        Topic topic = beg(id);
        while (topic != null) {
            ancestors.addFirst(topic);
            topic = topic.getUp() != null ? beg(topic.getUp().getId()) : null;
        }
        return ancestors;
    }

    public List<IdNameProjection> begNames(long rootId, long grp, boolean onlyAppendable, boolean onlyPostable) {
        Tree<Topic> tree = new Tree<>(begAll());
        List<IdNameProjection> names = new ArrayList<>();
        extractNames(names, tree, null, rootId, grp, onlyAppendable, onlyPostable);
        names.sort((np1, np2) -> np1.getName().compareToIgnoreCase(np2.getName()));
        return names;
    }

    private void extractNames(List<IdNameProjection> names, TreeNode<Topic> subtree, String prefix, long rootId,
                              long grp, boolean onlyAppendable, boolean onlyPostable) {
        if (prefix == null) { // Didn't find the root yet
            if (subtree.getId() == rootId || rootId <= 0 && subtree.getId() <= 0) {
                prefix = "";
            }
        } else { // Below the root
            Topic topic = subtree.getElement();
            if (prefix.equals("")) {
                prefix = topic.getSubject();
            } else {
                prefix = String.format("%s :: %s", prefix, topic.getSubject());
            }
            if ((grp < 0 || (topic.getGrp() & grp) != 0)
                    && (!onlyAppendable || topic.isAppendable())
                    && (!onlyPostable || topic.isPostable())) {
                names.add(new IdNameProjection(subtree.getId(), prefix));
            }
        }
        for (TreeNode<Topic> child : subtree.getChildren()) {
            extractNames(names, child, prefix, rootId, grp, onlyAppendable, onlyPostable);
        }
    }

    public int getSubtopicsCount(long id) {
        return topicRepository.countByUpId(id);
    }

    public void deleteTopic(Topic topic, Topic destTopic) {
        String oldTrack = topic.getTrack();
        if (destTopic != null) {
            entryRepository.updateUpId(topic.getId(), destTopic.getId());
            entryRepository.updateParentId(topic.getId(), destTopic.getId());
        }
        topicRepository.delete(topic);
        topicRepository.flush();
        if (destTopic != null) {
            catalogManager.updateCatalogs(oldTrack);
            trackManager.replaceTracks(oldTrack, destTopic.getTrack());
        }
    }

}