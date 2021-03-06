package ua.org.migdal.controller;

import javax.inject.Inject;

import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.RequestParam;

import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.location.LocationInfo;
import ua.org.migdal.manager.IdentManager;
import ua.org.migdal.manager.PostingManager;
import ua.org.migdal.manager.Postings;

@Controller
public class TextEarController {

    @Inject
    private PostingManager postingManager;

    @Inject
    private IdentManager identManager;

    @Inject
    private AdminController adminController;

    @Inject
    private PostingEditingController postingEditingController;

    @GetMapping("/admin/textears")
    public String adminTextEars(
            @RequestParam(defaultValue = "0") Integer offset,
            Model model) {
        adminTextEarsLocationInfo(model);

        long topicId = identManager.idOrIdent("textears");
        Postings p = Postings.all()
                             .topic(topicId, true)
                             .grp("TEXTEARS")
                             .page(offset, 20);
        model.addAttribute("postings", postingManager.begAll(p));
        return "admin-textears";
    }

    public LocationInfo adminTextEarsLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/admin/textears")
                .withTopics("topics-admin")
                .withTopicsIndex("admin-textears")
                .withParent(adminController.adminLocationInfo(null))
                .withPageTitle("Текстовые ушки");
    }

    @GetMapping("/admin/textears/add")
    public String textEarAdd(@RequestParam(required = false) boolean full, Model model) throws PageNotFoundException {
        textEarAddLocationInfo(model);

        return postingEditingController.postingAdd("TEXTEARS", 0, null, full, model);
    }

    public LocationInfo textEarAddLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/admin/textears/add")
                .withParent(adminTextEarsLocationInfo(null))
                .withPageTitle("Добавление ушка");
    }

    @GetMapping("/admin/textears/{id}/edit")
    public String textEarEdit(@PathVariable long id, @RequestParam(required = false) boolean full, Model model)
            throws PageNotFoundException {
        textEarEditLocationInfo(id, model);

        return postingEditingController.postingAddOrEdit(id, "TEXTEARS", 0, null, full, model);
    }

    public LocationInfo textEarEditLocationInfo(long id, Model model) {
        return new LocationInfo(model)
                .withUri("/admin/textears/" + id + "/edit")
                .withParent(adminTextEarsLocationInfo(null))
                .withPageTitle("Редактирование ушка");
    }

}