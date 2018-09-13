package ua.org.migdal.controller;

import javax.inject.Inject;

import org.springframework.data.domain.Sort;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;

import ua.org.migdal.data.Posting;
import ua.org.migdal.grp.GrpEnum;
import ua.org.migdal.location.GeneralViewFinder;
import ua.org.migdal.location.LocationInfo;
import ua.org.migdal.manager.PostingManager;

@Controller
public class BookController {

    @Inject
    private GrpEnum grpEnum;

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
        model.addAttribute("book", posting);
        model.addAttribute("bookChapters", postingManager.begAll(null, grpEnum.group("BOOK_CHAPTERS"), posting.getId(),
                null, null, 0, Integer.MAX_VALUE, Sort.Direction.ASC, "index0"));
        model.addAttribute("firstChapter", postingManager.begFirstByIndex0(posting.getId()));
        earController.addEars(model);

        return posting.getGrpDetailsTemplate();
    }

    public LocationInfo bookViewLocationInfo(Posting book, Model model) {
        LocationInfo generalView = generalViewFinder.findFor(book);
        return new LocationInfo(model)
                .withUri(book.getGrpDetailsHref())
                .withMenuMain(generalView.getMenuMain())
                .withTopics("topics-book")
                .withTopicsIndex(Long.toString(book.getId()))
                .withParent(generalView)
                .withPageTitle(book.getHeading());
    }

}
