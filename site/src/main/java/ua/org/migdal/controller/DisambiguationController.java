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
import ua.org.migdal.session.RequestContext;

@Controller
public class DisambiguationController {

    @Inject
    private GrpEnum grpEnum;

    @Inject
    private RequestContext requestContext;

    @Inject
    private PostingViewController postingViewController;

    @Inject
    private PostingEditingController postingEditingController;

    @Inject
    private IndexController indexController;

    @Inject
    private ForumController forumController;

    @Inject
    private PerUserController perUserController;

    @Inject
    private MigdalController migdalController;

    @GetMapping("/**/{smth:[^.]+$}") // $ in the regex is needed to match against the extension too
    public String disambiguate(
            @PathVariable String smth,
            Model model,
            @RequestParam(defaultValue = "0") Integer offset,
            @RequestParam(defaultValue = "0") Long tid,
            @RequestParam(required = false) boolean full,
            @RequestParam(defaultValue = "sent") String sort) throws PageNotFoundException {

        if (smth.equals("edit")) {
            return postingEditingController.postingEdit(full, model);
        }
        if (smth.startsWith("add-")) {
            return postingEditingController.postingAdd(smth.substring(4), full, model);
        }
        if (smth.startsWith("reorder-")) {
            return postingEditingController.postingsReorder(smth.substring(8), model);
        }
        if (requestContext.getCatalog().startsWith("taglit/")) {
            return perUserController.taglitUser(smth, offset, sort, model);
        }
        if (requestContext.getCatalog().startsWith("veterans/")) {
            return perUserController.veteransUser(smth, offset, sort, model);
        }
        return postingViewController.postingView(model, offset, tid);
    }

    public LocationInfo generalViewLocationInfo(Posting posting, Model model) {
        if (posting.getCatalog().startsWith("taglit/")) {
            return perUserController.taglitUserLocationInfo(posting.getUser(), model);
        }
        if (posting.getCatalog().startsWith("veterans/")) {
            return perUserController.veteransUserLocationInfo(posting.getUser(), model);
        }
        if (posting.getCatalog().startsWith("migdal/library/")) {
            return migdalController.libraryNoveltiesLocationInfo(model);
        }
        if (posting.getCatalog().startsWith("migdal/museum/")) {
            return migdalController.museumNewsLocationInfo(model);
        }
        if (posting.getCatalog().startsWith("migdal/mazltov/")) {
            return migdalController.mazltovNewsLocationInfo(model);
        }
        if (posting.getCatalog().startsWith("migdal/beitenu/")) {
            return migdalController.beitenuNewsLocationInfo(model);
        }
        if (posting.getCatalog().startsWith("migdal/methodology/")) {
            return migdalController.methodologyBooksLocationInfo(model);
        }
        if (posting.getCatalog().startsWith("migdal/printings/")) {
            return migdalController.printingsLocationInfo(model);
        }
        if (posting.getCatalog().startsWith("migdal/")) {
            return migdalController.migdalNewsLocationInfo(model);
        }
        if (posting.getGrp() == grpEnum.grpValue("FORUMS")) {
            return forumController.forumLocationInfo(model);
        }
        if (posting.getGrp() == grpEnum.grpValue("GALLERY")) {
            return indexController.majorGalleryLocationInfo(posting.getTopic(), model);
        }
        return indexController.majorLocationInfo(posting.getTopic(), model);
    }

}
