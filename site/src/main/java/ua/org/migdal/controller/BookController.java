package ua.org.migdal.controller;

import javax.inject.Inject;

import org.springframework.data.domain.Sort;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;

import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.data.Posting;
import ua.org.migdal.grp.GrpEnum;
import ua.org.migdal.location.LocationInfo;
import ua.org.migdal.manager.PostingManager;
import ua.org.migdal.manager.Postings;

@Controller
public class BookController {

    @Inject
    private GrpEnum grpEnum;

    @Inject
    private PostingManager postingManager;

    @Inject
    private DisambiguationController disambiguationController;

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
        model.addAttribute("firstChapter", postingManager.begFirstByIndex0(book.getId()));
        earController.addEars(model);

        return book.getGrpDetailsTemplate();
    }

    public LocationInfo bookViewLocationInfo(Posting book, Model model) {
        LocationInfo generalView = disambiguationController.generalViewLocationInfo(book, null);
        return new LocationInfo(model)
                .withUri(book.getGrpDetailsHref())
                .withMenuMain(generalView.getMenuMain())
                .withTopics("topics-book", book)
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
        model.addAttribute("prevChapter", postingManager.begNextByIndex0(book.getId(), chapter.getIndex0(), false));
        model.addAttribute("nextChapter", postingManager.begNextByIndex0(book.getId(), chapter.getIndex0(), true));
        earController.addEars(model);

        return chapter.getGrpDetailsTemplate();
    }

    public LocationInfo bookChapterViewLocationInfo(Posting chapter, Posting book, Model model) {
        LocationInfo generalView = disambiguationController.generalViewLocationInfo(book, null);
        return new LocationInfo(model)
                .withUri(chapter.getGrpDetailsHref())
                .withMenuMain(generalView.getMenuMain())
                .withTopics("topics-book", chapter)
                .withTopicsIndex(Long.toString(chapter.getId()))
                .withParent(bookViewLocationInfo(book, null))
                .withPageTitle(book.getHeading() + " - " + chapter.getHeading())
                .withPageTitleRelative(chapter.getHeading());
    }

    @TopicsMapping("topics-book")
    protected void addBook(Posting posting, Model model) {
        Posting book;
        if (posting.getGrp() == grpEnum.grpValue("BOOKS")) {
            book = posting;
        } else {
            book = postingManager.beg(posting.getUp().getId());
        }
        model.addAttribute("book", book);
        Postings p = Postings.all()
                .grp("BOOK_CHAPTERS")
                .up(book.getId())
                .sort(Sort.Direction.ASC, "index0");
        model.addAttribute("bookChapters", postingManager.begAll(p));
    }

}
