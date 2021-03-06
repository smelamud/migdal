package ua.org.migdal.controller;

import javax.inject.Inject;

import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.RequestParam;

import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.data.Posting;
import ua.org.migdal.data.Topic;
import ua.org.migdal.grp.GrpEnum;
import ua.org.migdal.location.LocationInfo;
import ua.org.migdal.manager.IdentManager;
import ua.org.migdal.manager.PostingManager;
import ua.org.migdal.manager.TopicManager;
import ua.org.migdal.session.RequestContext;
import ua.org.migdal.util.Utils;

@Controller
public class DisambiguationController {

    @Inject
    private GrpEnum grpEnum;

    @Inject
    private RequestContext requestContext;

    @Inject
    private IdentManager identManager;

    @Inject
    private TopicManager topicManager;

    @Inject
    private PostingManager postingManager;

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

    @Inject
    private EventController eventController;

    @Inject
    private EnglishController englishController;

    @Inject
    private BookController bookController;

    @Inject
    private TimesController timesController;

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
        if (smth.equals("print")) {
            return bookController.bookPrint(model);
        }
        if (requestContext.getCatalogLength() <= 2) {
            if (requestContext.getCatalog().startsWith("taglit/")) {
                return perUserController.taglitUser(smth, offset, sort, model);
            }
            if (requestContext.getCatalog().startsWith("veterans/")) {
                return perUserController.veteransUser(smth, offset, sort, model);
            }
        }
        if (requestContext.getCatalogLength() == 5
                && requestContext.getCatalog().startsWith("migdal/events/")) {
            return eventController.eventsTypeSubtypeId(
                    requestContext.getCatalogElement(2),
                    requestContext.getCatalogElement(3),
                    requestContext.getCatalogElement(4),
                    offset,
                    tid,
                    model);
        }
        if (requestContext.isEnglish()
                && requestContext.getCatalogLength() == 2
                && requestContext.getCatalog().startsWith("migdal/")
                && Utils.isNumber(requestContext.getCatalogElement(1))) {
            return englishController.migdal(Long.parseLong(requestContext.getCatalogElement(1)), model);
        }
        return postingViewController.postingView(model, offset, tid);
    }

    public LocationInfo generalViewLocationInfo(Posting posting, Model model) {
        if (posting.getGrp() == grpEnum.grpValue("BOOK_CHAPTERS")) {
            Posting book = postingManager.beg(posting.getUpId());
            return postingViewController.generalPostingViewLocationInfo(book, model);
        }
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
            if (posting.getGrp() != grpEnum.grpValue("GALLERY")) {
                return migdalController.museumNewsLocationInfo(model);
            } else {
                return migdalController.museumGalleryLocationInfo(posting.getTopic(), model);
            }
        }
        if (posting.getCatalog().startsWith("migdal/mazltov/")) {
            if (posting.getGrp() != grpEnum.grpValue("GALLERY")) {
                return migdalController.mazltovNewsLocationInfo(model);
            } else {
                return migdalController.mazltovGalleryLocationInfo(posting.getTopic(), model);
            }
        }
        if (posting.getCatalog().startsWith("migdal/beitenu/")) {
            if (posting.getGrp() != grpEnum.grpValue("GALLERY")) {
                return migdalController.beitenuNewsLocationInfo(model);
            } else {
                return migdalController.beitenuGalleryLocationInfo(posting.getTopic(), model);
            }
        }
        if (posting.getCatalog().startsWith("migdal/methodology/")) {
            return migdalController.methodologyBooksLocationInfo(model);
        }
        if (posting.getCatalog().startsWith("migdal/printings/")) {
            return migdalController.printingsLocationInfo(model);
        }
        if (posting.getCatalog().startsWith("migdal/events/")) {
            Topic topic = topicManager.beg(identManager.topicIdFromRequestPath(0, -1));
            if (topic != null && topic.accepts("DAILY_NEWS")) {
                if (posting.getGrp() == grpEnum.grpValue("DAILY_GALLERY")) {
                    return eventController.dailyEventNewsLocationInfo(posting, model);
                } else {
                    return eventController.dailyEventLocationInfo(posting, model);
                }
            }
            if (posting.getGrp() == grpEnum.grpValue("GALLERY")) {
                return eventController.regularEventGalleryLocationInfo(posting.getTopic(), model);
            }
            return eventController.regularEventLocationInfo(posting.getTopic(), model);
        }
        if (posting.getCatalog().startsWith("migdal/")) {
            return migdalController.migdalNewsLocationInfo(model);
        }
        if (posting.getGrp() == grpEnum.grpValue("TIMES_ARTICLES")) {
            return timesController.timesIssueLocationInfo(posting.getIndex1(), model);
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
