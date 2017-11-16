package ua.org.migdal.controller;

import java.util.stream.Collectors;
import javax.inject.Inject;

import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.data.CrossEntry;
import ua.org.migdal.data.LinkType;
import ua.org.migdal.data.Topic;
import ua.org.migdal.manager.CrossEntryManager;
import ua.org.migdal.manager.IdentManager;
import ua.org.migdal.manager.TopicManager;
import ua.org.migdal.session.LocationInfo;

@Controller
public class IndexController {

    @Inject
    private IdentManager identManager;

    @Inject
    private CrossEntryManager crossEntryManager;

    @Inject
    private TopicManager topicManager;

    @GetMapping("/")
    public String index(Model model) {
        indexLocationInfo(model);

        model.addAttribute("topic", null);
        addMajors(model);
        return "index-www";
    }

    public LocationInfo indexLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/")
                .withRssHref("/rss/")
                .withTranslationHref("/")
                .withTopics("topics-major")
                .withTopicsIndex("index")
                .withPageTitle("Главная");
    }

    @GetMapping("/{ident}")
    public String major(@PathVariable String ident, Model model) throws PageNotFoundException {
        long id = identManager.idOrIdent(ident);
        if (id <= 0) {
            throw new PageNotFoundException();
        }
        Topic topic = topicManager.beg(id);
        if (topic == null) {
            throw new PageNotFoundException();
        }

        majorLocationInfo(topic, model);

        model.addAttribute("topic", topic);
        addMajors(model);
        return "index-www";
    }

    public LocationInfo majorLocationInfo(Topic topic, Model model) {
        return new LocationInfo(model)
                .withUri("/" + topic.getIdent())
                .withRssHref("/rss/")
                .withTopics("topics-major")
                .withTopicsIndex(topic.getIdent())
                .withPageTitle(topic.getSubject());
    }

    private void addMajors(Model model) {
        model.addAttribute("majors", crossEntryManager.getAll(LinkType.MAJOR, "major")
                                                      .stream()
                                                      .map(CrossEntry::getPeer)
                                                      .collect(Collectors.toList()));
    }

}
