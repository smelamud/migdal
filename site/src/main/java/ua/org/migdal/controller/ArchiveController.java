package ua.org.migdal.controller;

import javax.inject.Inject;

import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestParam;

import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.data.Topic;
import ua.org.migdal.location.LocationInfo;
import ua.org.migdal.manager.IdentManager;
import ua.org.migdal.manager.TopicManager;

@Controller
public class ArchiveController {

    @Inject
    private IdentManager identManager;

    @Inject
    private TopicManager topicManager;

    @Inject
    private IndexController indexController;

    @GetMapping("/tips")
    public String tips(
            @RequestParam(defaultValue = "0") Integer offset,
            Model model) throws PageNotFoundException {

        Topic topic = topicManager.beg(identManager.idOrIdent("tips"));
        if (topic == null) {
            throw new PageNotFoundException();
        }

        tipsLocationInfo(model);

        indexController.addPostings("TIPS", topic, null, new String[] {"TIPS"}, false, offset, 20, model);

        return "tips";
    }

    public LocationInfo tipsLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/tips")
                .withTopics("topics-major")
                .withTopicsIndex("tips")
                .withParent(indexController.indexLocationInfo(null))
                .withPageTitle("Знаете ли вы, что...");
    }

}
