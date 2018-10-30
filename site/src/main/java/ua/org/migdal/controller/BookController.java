package ua.org.migdal.controller;

import javax.inject.Inject;

import org.springframework.data.domain.Sort;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;

import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.data.Posting;
import ua.org.migdal.grp.GrpEnum;
import ua.org.migdal.manager.PostingManager;
import ua.org.migdal.manager.Postings;

@Controller
public class BookController {

    @Inject
    private GrpEnum grpEnum;

    @Inject
    private PostingManager postingManager;

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

}
