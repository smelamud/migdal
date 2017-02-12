package ua.org.migdal.controller;

import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;

import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestParam;
import ua.org.migdal.session.LocationInfo;

@Controller
public class SearchController {

    @GetMapping("/search")
    public String search(@RequestParam(required = false) String q, Model model) {
        searchLocationInfo(model, q);

        model.addAttribute("q", q);

        return "search-google";
    }

    public static LocationInfo searchLocationInfo(Model model) {
        return searchLocationInfo(model, null);
    }

    public static LocationInfo searchLocationInfo(Model model, String q) {
        return new LocationInfo(model)
                .withParent(IndexController.indexLocationInfo(null))
                .withMenuMain("search")
                .withPageTitle(q == null ? "Поиск" : "Поиск: " + q);
    }

}
