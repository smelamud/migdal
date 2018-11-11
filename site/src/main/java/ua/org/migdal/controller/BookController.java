package ua.org.migdal.controller;

import java.util.ArrayList;
import java.util.List;
import javax.inject.Inject;

import org.springframework.data.domain.Sort;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;

import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.data.Image;
import ua.org.migdal.data.InnerImage;
import ua.org.migdal.data.Posting;
import ua.org.migdal.grp.GrpEnum;
import ua.org.migdal.location.LocationInfo;
import ua.org.migdal.manager.IdentManager;
import ua.org.migdal.manager.InnerImageManager;
import ua.org.migdal.manager.PostingManager;
import ua.org.migdal.manager.Postings;
import ua.org.migdal.session.RequestContext;

@Controller
public class BookController {

    @Inject
    private GrpEnum grpEnum;

    @Inject
    private RequestContext requestContext;

    @Inject
    private IdentManager identManager;

    @Inject
    private PostingManager postingManager;

    @Inject
    private InnerImageManager innerImageManager;

    @Inject
    private PostingViewController postingViewController;

    @DetailsMapping("book")
    protected void bookView(Posting book, Model model) {
        model.addAttribute("firstChapter", postingManager.begFirstByIndex0(book.getId()));
    }

    @DetailsMapping("book-chapter")
    protected void bookChapterView(Posting chapter, Model model) throws PageNotFoundException {
        Posting book = postingManager.beg(chapter.getUp().getId());
        if (book == null) {
            throw new PageNotFoundException();
        }

        model.addAttribute("book", book);
        model.addAttribute("prevChapter", postingManager.begNextByIndex0(book.getId(), chapter.getIndex0(), false));
        model.addAttribute("nextChapter", postingManager.begNextByIndex0(book.getId(), chapter.getIndex0(), true));
    }

    @TopicsMapping("topics-book")
    protected void topicsBook(Posting posting, Model model) {
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

    // @GetMapping("/**/{book}/print")
    String bookPrint(Model model) throws PageNotFoundException {
        Posting book = postingManager.beg(identManager.postingIdFromRequestPath(0, -1));
        if (book == null || book.getGrp() != grpEnum.grpValue("BOOKS")) {
            throw new PageNotFoundException();
        }

        bookPrintLocationInfo(book, model);

        if (book.isHasImage()) {
            requestContext.addOgImage(book.getImageUrl());
        }
        Postings p = Postings.all().grp("BOOK_CHAPTERS").up(book.getId()).sort(Sort.Direction.ASC, "index0");
        List<Posting> bookChapters = postingManager.begAllAsList(p);
        List<InnerImage> innerImages = new ArrayList<>();
        bookChapters.stream()
                .map(Posting::getId)
                .map(innerImageManager::getAll)
                .flatMap(List::stream)
                .forEach(innerImages::add);
        innerImages.stream()
                .map(InnerImage::getImage)
                .map(Image::getImageUrl)
                .forEach(requestContext::addOgImage);

        model.addAttribute("book", book);
        model.addAttribute("bookChapters", bookChapters);
        model.addAttribute("innerImages", innerImages);

        return "book-print";
    }

    public LocationInfo bookPrintLocationInfo(Posting book, Model model) {
        return new LocationInfo(model)
                .withUri(book.getGrpDetailsHref() + "/print")
                .withTopics("topics-book", book)
                .withTopicsIndex("print")
                .withParent(postingViewController.generalPostingViewLocationInfo(book, null))
                .withPageTitle(book.getHeading() + " - Вся книга для печати")
                .withPageTitleRelative("Вся книга для печати");
    }

}
