package ua.org.migdal.controller;

import javax.inject.Inject;

import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import ua.org.migdal.form.CrossEntryAddForm;
import ua.org.migdal.session.LocationInfo;

@Controller
public class CrossEntryController {

    @Inject
    private IndexController indexController;

    @GetMapping("/add-cross")
    public String crossAdd(Model model) {
        crossAddLocationInfo(model);

        model.asMap().computeIfAbsent("crossEntryAddForm", key -> new CrossEntryAddForm());
        return "cross-entry-add";
    }

    public LocationInfo crossAddLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/add-cross")
                .withParent(indexController.indexLocationInfo(null))
                .withPageTitle("Добавление перекрестной ссылки");
    }

}
