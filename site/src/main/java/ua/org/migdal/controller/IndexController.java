package ua.org.migdal.controller;

import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import ua.org.migdal.session.LocationInfo;

@Controller
public class IndexController {

    @GetMapping("/")
    public String index(Model model) {
        indexLocationInfo(model);

        return "index-www";
    }

    public LocationInfo indexLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/")
                .withRssHref("/rss/")
                .withTranslationHref("/")
                .withTopics("topicsMajor")
                .withTopicsIndex("index")
                .withPageTitle("Главная");
    }

}
