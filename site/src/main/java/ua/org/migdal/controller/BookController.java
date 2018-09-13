package ua.org.migdal.controller;

import javax.inject.Inject;

import org.springframework.data.domain.Sort;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;

import ua.org.migdal.controller.exception.PageNotFoundException;
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
            Posting book,
            Model model,
            Integer offset,
            Long tid) {

        bookViewLocationInfo(book, model);

        postingViewController.addPostingView(model, book, offset, tid);
        model.addAttribute("book", book);
        model.addAttribute("bookChapters", postingManager.begAll(null, grpEnum.group("BOOK_CHAPTERS"), book.getId(),
                null, null, 0, Integer.MAX_VALUE, Sort.Direction.ASC, "index0"));
        model.addAttribute("firstChapter", postingManager.begFirstByIndex0(book.getId()));
        earController.addEars(model);

        return book.getGrpDetailsTemplate();
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

    String bookChapterView(
            Posting chapter,
            Model model,
            Integer offset,
            Long tid) throws PageNotFoundException {

        Posting book = postingManager.beg(chapter.getUp().getId());
        if (book == null) {
            throw new PageNotFoundException();
        }

        bookChapterViewLocationInfo(chapter, book, model);

        postingViewController.addPostingView(model, chapter, offset, tid);
        model.addAttribute("book", book);
        model.addAttribute("bookChapters", postingManager.begAll(null, grpEnum.group("BOOK_CHAPTERS"), book.getId(),
                null, null, 0, Integer.MAX_VALUE, Sort.Direction.ASC, "index0"));
        model.addAttribute("prevChapter", postingManager.begNextByIndex0(book.getId(), chapter.getIndex0(), false));
        model.addAttribute("nextChapter", postingManager.begNextByIndex0(book.getId(), chapter.getIndex0(), true));
        earController.addEars(model);

        return chapter.getGrpDetailsTemplate();
    }

    public LocationInfo bookChapterViewLocationInfo(Posting chapter, Posting book, Model model) {
        LocationInfo generalView = generalViewFinder.findFor(book);
        return new LocationInfo(model)
                .withUri(chapter.getGrpDetailsHref())
                .withMenuMain(generalView.getMenuMain())
                .withTopics("topics-book")
                .withTopicsIndex(Long.toString(chapter.getId()))
                .withParent(bookViewLocationInfo(book, null))
                .withPageTitle(book.getHeading() + " - " + chapter.getHeading())
                .withPageTitleRelative(chapter.getHeading());
    }

}
