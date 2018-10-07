package ua.org.migdal.controller;

import javax.inject.Inject;

import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.RequestParam;

import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.data.Posting;
import ua.org.migdal.grp.GrpEnum;
import ua.org.migdal.location.LocationInfo;

@Controller
public class DisambiguationController {

    @Inject
    private GrpEnum grpEnum;

    @Inject
    private PostingViewController postingViewController;

    @Inject
    private PostingEditingController postingEditingController;

    @Inject
    private IndexController indexController;

    @Inject
    private ForumController forumController;

    @GetMapping("/**/{smth:[^.]+$}") // $ in the regex is needed to match against the extension too
    public String disambiguate(
            @PathVariable String smth,
            Model model,
            @RequestParam(defaultValue = "0") Integer offset,
            @RequestParam(defaultValue = "0") Long tid,
            @RequestParam(required = false) boolean full) throws PageNotFoundException {

        if (smth.equals("edit")) {
            return postingEditingController.postingEdit(full, model);
        }
        if (smth.startsWith("add-")) {
            return postingEditingController.postingAdd(smth.substring(4), full, model);
        }
        if (smth.startsWith("reorder-")) {
            return postingEditingController.postingsReorder(smth.substring(8), model);
        }
        return postingViewController.postingView(model, offset, tid);
    }

    public LocationInfo generalViewLocationInfo(Posting posting, Model model) {
        if (posting.getGrp() == grpEnum.grpValue("FORUMS")) {
            return forumController.forumLocationInfo(model);
        }
        if (posting.getGrp() == grpEnum.grpValue("GALLERY")) {
            return indexController.majorGalleryLocationInfo(posting.getTopic(), model);
        }
        return indexController.majorLocationInfo(posting.getTopic(), model);
    }

}
