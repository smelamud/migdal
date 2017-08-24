package ua.org.migdal.controller;

import javax.inject.Inject;

import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;

import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.data.Topic;
import ua.org.migdal.form.ChmodForm;
import ua.org.migdal.manager.TopicManager;
import ua.org.migdal.session.LocationInfo;

@Controller
public class EntryController {

    @Inject
    private TopicManager topicManager;

    @Inject
    private TopicController topicController;

    @GetMapping("/admin/topics/**/{id}/chmod")
    public String topicChmod(@PathVariable long id, Model model) throws PageNotFoundException {
        Topic topic = topicManager.beg(id);
        if (topic == null) {
            throw new PageNotFoundException();
        }

        topicChmodLocationInfo(topic, model);

        model.asMap().putIfAbsent("chmodForm", new ChmodForm(topic));
        return "chmod";
    }

    public LocationInfo topicChmodLocationInfo(Topic topic, Model model) {
        return new LocationInfo(model)
                .withUri("/admin/topics/" + topic.getTrackPath() + "chmod")
                .withParent(topicController.adminTopicsLocationInfo(null))
                .withPageTitle("Изменение прав на тему");
    }

}