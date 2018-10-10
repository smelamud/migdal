package ua.org.migdal.controller;

import java.util.Set;

import javax.inject.Inject;

import org.springframework.data.domain.Sort;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.RequestParam;

import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.data.Posting;
import ua.org.migdal.data.VoteType;
import ua.org.migdal.location.LocationInfo;
import ua.org.migdal.manager.IdentManager;
import ua.org.migdal.manager.PostingManager;
import ua.org.migdal.manager.Postings;
import ua.org.migdal.manager.VoteManager;

@Controller
public class EarController {

    @Inject
    private PostingManager postingManager;

    @Inject
    private IdentManager identManager;

    @Inject
    private VoteManager voteManager;

    @Inject
    private AdminController adminController;

    @Inject
    private PostingEditingController postingEditingController;

    @GetMapping("/admin/ears")
    public String adminEars(
            @RequestParam(defaultValue = "0") Integer offset,
            Model model) {
        adminEarsLocationInfo(model);

        long topicId = identManager.idOrIdent("ears");
        Postings p = Postings.all()
                             .topic(topicId, true)
                             .grp("EARS")
                             .page(offset, 20)
                             .sort(Sort.Direction.DESC, "ratio", "counter0", "sent");
        model.addAttribute("postings", postingManager.begAll(p));
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
    public String earAdd(@RequestParam(required = false) boolean full, Model model) throws PageNotFoundException {
        earAddLocationInfo(model);

        return postingEditingController.postingAdd("EARS", 0, null, full, model);
    }

    public LocationInfo earAddLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/admin/ears/add")
                .withParent(adminEarsLocationInfo(null))
                .withPageTitle("Добавление ушка");
    }

    @GetMapping("/admin/ears/{id}/edit")
    public String earEdit(@PathVariable long id, @RequestParam(required = false) boolean full, Model model)
            throws PageNotFoundException {
        earEditLocationInfo(id, model);

        return postingEditingController.postingAddOrEdit(id, "EARS", 0, null, full, model);
    }

    public LocationInfo earEditLocationInfo(long id, Model model) {
        return new LocationInfo(model)
                .withUri("/admin/ears/" + id + "/edit")
                .withParent(adminEarsLocationInfo(null))
                .withPageTitle("Редактирование ушка");
    }

    @GetMapping("/actions/ear/{id}")
    public String actionEarClick(@PathVariable long id) throws PageNotFoundException {
        Posting posting = postingManager.beg(id);
        if (posting == null) {
            throw new PageNotFoundException();
        }
        voteManager.vote(posting, VoteType.CLICK, 1);
        postingManager.save(posting);
        return "redirect:" + posting.getUrl();
    }

    public void addEars(Model model) {
        Set<Posting> ears = postingManager.begRandomWithPriorities(Postings.all().grp("EARS").limit(3));
        ears.forEach(posting -> {
            voteManager.vote(posting, VoteType.VIEW, 1);
            postingManager.save(posting);
        });
        model.addAttribute("ears", ears);
    }

}
