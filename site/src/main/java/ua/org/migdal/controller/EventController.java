package ua.org.migdal.controller;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import javax.inject.Inject;

import org.springframework.data.domain.Sort;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;

import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestParam;
import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.data.EntryType;
import ua.org.migdal.data.Topic;
import ua.org.migdal.data.util.TreeNode;
import ua.org.migdal.location.LocationInfo;
import ua.org.migdal.manager.IdentManager;
import ua.org.migdal.manager.PostingManager;
import ua.org.migdal.manager.Postings;
import ua.org.migdal.manager.TopicManager;

@Controller
public class EventController {

    @Inject
    private IdentManager identManager;

    @Inject
    private TopicManager topicManager;

    @Inject
    private PostingManager postingManager;

    @Inject
    private MigdalController migdalController;

    @Inject
    private EntryController entryController;

    @GetMapping("/migdal/events")
    public String events(Model model) throws PageNotFoundException {
        long eventsId = identManager.idOrIdent("migdal.events");
        if (eventsId <= 0) {
            throw new PageNotFoundException();
        }

        eventsLocationInfo(model);

        Iterable<Topic> events = topicManager.begAll(eventsId, true, Sort.Direction.DESC, "index2", "index0");

        List<Topic> lastEvents = new ArrayList<>();
        List<TreeNode<Topic>> eventGroups = new ArrayList<>();
        Map<Long, TreeNode<Topic>> eventGroupMap = new HashMap<>();

        for (Topic topic : events) {
            if (lastEvents.size() < 5) {
                lastEvents.add(topic);
            }
            if (topic.getUpId() == eventsId) {
                TreeNode<Topic> node = new TreeNode<>(topic.getId(), topic);
                eventGroups.add(node);
                eventGroupMap.put(topic.getId(), node);
            }
        }

        model.addAttribute("lastEvents", lastEvents);

        for (Topic topic : events) {
            if (topic.getUpId() != eventsId) {
                TreeNode<Topic> group = eventGroupMap.get(topic.getUpId());
                if (group != null) {
                    group.getChildren().add(new TreeNode<>(topic.getId(), topic));
                }
            }
        }
        eventGroups.sort(
                (node1, node2) -> node1.getElement().getSubject().compareToIgnoreCase(node2.getElement().getSubject()));
        model.addAttribute("eventGroups", eventGroups);

        Postings p = Postings.all().grp("GRAPHICS").asGuest();
        lastEvents.forEach(t -> p.topic(t.getId()));
        model.addAttribute("picture", postingManager.begRandomOne(p));

        return "events";
    }

    public LocationInfo eventsLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/migdal/events")
                .withTopics("topics-events")
                .withTopicsIndex("migdal.events")
                .withParent(migdalController.migdalLocationInfo(null))
                .withPageTitle("События")
                .withTranslationHref("/events");
    }

    @TopicsMapping("topics-events")
    protected void addEvents(Model model) {
        model.addAttribute("events", topicManager.begAll(
                identManager.idOrIdent("migdal.events"), true, Sort.Direction.DESC, "index2", "index0"));
    }

    @GetMapping("/migdal/events/reorder")
    public String eventsReorder(
            @RequestParam Long year,
            Model model) {

        eventsReorderLocationInfo(model);

        Iterable<Topic> topics = topicManager.begAll(
                identManager.idOrIdent("migdal.events"), true, year, Sort.Direction.ASC, "index0");
        return entryController.entryReorder(topics, EntryType.TOPIC, model);
    }

    public LocationInfo eventsReorderLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/migdal/printings/reorder")
                .withParent(eventsLocationInfo(null))
                .withPageTitle("Расстановка событий");
    }

}
