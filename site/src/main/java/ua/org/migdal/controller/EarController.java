package ua.org.migdal.controller;

import java.util.Collections;

import javax.inject.Inject;

import org.springframework.data.domain.Sort;
import org.springframework.data.util.Pair;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.RequestParam;

import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.grp.GrpEnum;
import ua.org.migdal.manager.IdentManager;
import ua.org.migdal.manager.PostingManager;
import ua.org.migdal.session.LocationInfo;

@Controller
public class EarController {

    @Inject
    private PostingManager postingManager;

    @Inject
    private IdentManager identManager;

    @Inject
    private GrpEnum grpEnum;

    @Inject
    private AdminController adminController;

    @Inject
    private PostingController postingController;

    @GetMapping("/admin/ears")
    public String adminPostings(
            @RequestParam(defaultValue = "0") Integer offset,
            Model model) {
        adminEarsLocationInfo(model);

        long topicId = identManager.getIdByIdent("ears");
        model.addAttribute("postings",
                        postingManager.begAll(
                                Collections.singletonList(Pair.of(topicId, true)),
                                grpEnum.group("EARS"),
                                offset,
                                20,
                                Sort.Direction.DESC,
                                "ratio",
                                "counter0",
                                "sent"));
        return "admin-ears";
    }

    public LocationInfo adminEarsLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/admin/ears")
                .withTopics("topics-admin")
                .withTopicsIndex("admin-ears")
                .withParent(adminController.adminLocationInfo(null))
                .withPageTitle("Ушки");
    }

    @GetMapping("/admin/ears/add")
    public String postingAdd(@RequestParam(required = false) boolean full, Model model) throws PageNotFoundException {
        postingAddLocationInfo(model);

        return postingController.postingAddOrEdit(null, "EARS", full, model);
    }

    public LocationInfo postingAddLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/admin/ears/add")
                .withParent(adminEarsLocationInfo(null))
                .withPageTitle("Добавление ушка");
    }

    @GetMapping("/admin/ears/{id}/edit")
    public String postingEdit(@PathVariable long id, @RequestParam(required = false) boolean full, Model model)
            throws PageNotFoundException {
        postingEditLocationInfo(id, model);

        return postingController.postingAddOrEdit(id, "EARS", full, model);
    }

    public LocationInfo postingEditLocationInfo(long id, Model model) {
        return new LocationInfo(model)
                .withUri("/admin/ears/" + id + "/edit")
                .withParent(adminEarsLocationInfo(null))
                .withPageTitle("Редактирование ушка");
    }

}