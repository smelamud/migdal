package ua.org.migdal.controller;

import javax.inject.Inject;

import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;

import ua.org.migdal.data.Posting;
import ua.org.migdal.location.GeneralViewFinder;
import ua.org.migdal.location.LocationInfo;
import ua.org.migdal.manager.PostingManager;

@Controller
public class BookController {

    @Inject
    private PostingManager postingManager;

    @Inject
    private GeneralViewFinder generalViewFinder;

    @Inject
    private PostingViewController postingViewController;

    @Inject
    private EarController earController;

    String bookView(
            Posting posting,
            Model model,
            Integer offset,
            Long tid) {

        bookViewLocationInfo(posting, model);

        postingViewController.addPostingView(model, posting, offset, tid);
        model.addAttribute("firstChapter", postingManager.begFirstByIndex0(posting.getId()));
        earController.addEars(model);

        return posting.getGrpDetailsTemplate();
    }

    public LocationInfo bookViewLocationInfo(Posting posting, Model model) {
        LocationInfo generalView = generalViewFinder.findFor(posting);
        return new LocationInfo(model)
                .withUri(posting.getGrpDetailsHref())
                .withMenuMain(generalView.getMenuMain())
                .withTopics(generalView.getTopics())
                .withTopicsIndex(generalView.getTopicsIndex())
                .withParent(generalView)
                .withPageTitle(posting.getHeading());
    }

}
