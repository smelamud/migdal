package ua.org.migdal.controller;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;

import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestParam;
import ua.org.migdal.session.LocationInfo;

@Controller
public class SearchController {

    @Autowired
    private IndexController indexController;

    @GetMapping("/search")
    public String search(@RequestParam(required = false) String q, Model model) {
        searchLocationInfo(model, q);

        model.addAttribute("q", q);

        return "search-google";
    }

    public LocationInfo searchLocationInfo(Model model, String q) {
        return new LocationInfo(model)
                .withUri("/search")
                .withParent(indexController.indexLocationInfo(null))
                .withMenuMain("search")
                .withPageTitle(q == null ? "Поиск" : "Поиск: " + q);
    }

}
