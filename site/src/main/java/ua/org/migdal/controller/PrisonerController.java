package ua.org.migdal.controller;

import javax.inject.Inject;

import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.util.StringUtils;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestParam;

import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.helper.util.Constant;
import ua.org.migdal.location.LocationInfo;
import ua.org.migdal.manager.PrisonerManager;

@Controller
public class PrisonerController {

    private static final Constant[] SORTS = new Constant[] {
            new Constant<>("по имени", "name"),
            new Constant<>("по русскому имени", "nameRussian"),
            new Constant<>("по месту", "location"),
            new Constant<>("по гетто", "ghettoName"),
            new Constant<>("по сообщившему", "senderName")
    };

    @Inject
    private PrisonerManager prisonerManager;

    @Inject
    private EarController earController;

    @Inject
    private MigdalController migdalController;

    @GetMapping("/migdal/museum/prisoners")
    public String prisoners(
            @RequestParam(defaultValue="") String prefix,
            @RequestParam(defaultValue="name") String sort,
            @RequestParam(defaultValue="0") Integer offset,
            Model model) throws PageNotFoundException {

        prisonersLocationInfo(model);

        if (!StringUtils.isEmpty(sort) && !Constant.hasValue(SORTS, sort)) {
            throw new PageNotFoundException();
        }

        model.addAttribute("prefix", prefix);
        model.addAttribute("sort", sort);
        model.addAttribute("sorts", SORTS);
        model.addAttribute("prisoners", prisonerManager.begAll(prefix, sort, offset, 50));
        earController.addEars(model);
        migdalController.addMuseum(model);

        return "prisoners";
    }

    public LocationInfo prisonersLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/migdal/museum/prisoners")
                .withTopics("topics-museum")
                .withTopicsIndex("migdal.museum.prisoners")
                .withParent(migdalController.museumLocationInfo(null))
                .withPageTitle("Узники гетто");
    }

}
