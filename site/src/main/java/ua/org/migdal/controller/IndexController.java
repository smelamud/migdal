package ua.org.migdal.controller;

import java.util.stream.Collectors;
import javax.inject.Inject;

import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import ua.org.migdal.data.CrossEntry;
import ua.org.migdal.data.LinkType;
import ua.org.migdal.manager.CrossEntryManager;
import ua.org.migdal.session.LocationInfo;

@Controller
public class IndexController {

    @Inject
    private CrossEntryManager crossEntryManager;

    @GetMapping("/")
    public String index(Model model) {
        indexLocationInfo(model);

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

    private void addMajors(Model model) {
        model.addAttribute("majors", crossEntryManager.getAll(LinkType.MAJOR, "major")
                                                      .stream()
                                                      .map(CrossEntry::getPeer)
                                                      .collect(Collectors.toList()));
    }

}
