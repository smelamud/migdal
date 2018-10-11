package ua.org.migdal.controller;

import java.util.List;
import java.util.stream.Collectors;

import javax.inject.Inject;

import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.RequestParam;

import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.data.CrossEntry;
import ua.org.migdal.data.Image;
import ua.org.migdal.data.InnerImage;
import ua.org.migdal.data.LinkType;
import ua.org.migdal.data.Posting;
import ua.org.migdal.form.ForumForm;
import ua.org.migdal.grp.GrpEnum;
import ua.org.migdal.location.LocationInfo;
import ua.org.migdal.manager.CrossEntryManager;
import ua.org.migdal.manager.ForumManager;
import ua.org.migdal.manager.IdentManager;
import ua.org.migdal.manager.InnerImageManager;
import ua.org.migdal.manager.PostingManager;
import ua.org.migdal.session.RequestContext;

@Controller
public class PostingViewController {

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
    private ForumManager forumManager;

    @Inject
    private CrossEntryManager crossEntryManager;

    @Inject
    private DisambiguationController disambiguationController;

    @Inject
    private IndexController indexController;

    @Inject
    private BookController bookController;

    @Inject
    private EarController earController;

    @Inject
    private PerUserController perUserController;

    // @GetMapping("/**/{id or ident}")
    public String postingView(
            Model model,
            @RequestParam(defaultValue = "0") Integer offset,
            @RequestParam(defaultValue = "0") Long tid) throws PageNotFoundException {

        long id = identManager.postingIdFromRequestPath();
        Posting posting = postingManager.beg(id);
        if (posting == null) {
            throw new PageNotFoundException();
        }

        if (posting.getGrp() == grpEnum.grpValue("BOOKS")) {
            return bookController.bookView(posting, model, offset, tid);
        } else if (posting.getGrp() == grpEnum.grpValue("BOOK_CHAPTERS")) {
            return bookController.bookChapterView(posting, model, offset, tid);
        } else {
            return generalPostingView(posting, model, offset, tid);
        }
    }

    private String generalPostingView(
            Posting posting,
            Model model,
            Integer offset,
            Long tid) {

        LocationInfo locationInfo = generalPostingViewLocationInfo(posting, model);

        addPostingView(model, posting, offset, tid);
        if (locationInfo.getTopics().equals("topics-per-user")) {
            perUserController.addOwners(posting.getTopic(), model);
        }
        indexController.addMajors(model);
        earController.addEars(model);

        return posting.getGrpDetailsTemplate();
    }

    void addPostingView(Model model, Posting posting, Integer offset, Long tid) {
        if (posting.isHasImage()) {
            requestContext.addOgImage(posting.getImageUrl());
        }

        model.addAttribute("posting", posting);
        if (posting.isGrpPublisher()) {
            posting.setPublishedEntries(
                    crossEntryManager.getAll(LinkType.PUBLISH, posting.getId()).stream()
                            .map(CrossEntry::getPeer)
                            .collect(Collectors.toList()));
        }
        if (posting.isGrpInnerImages()) {
            List<InnerImage> innerImages = innerImageManager.getAll(posting.getId());
            innerImages.stream()
                    .map(InnerImage::getImage)
                    .map(Image::getImageUrl)
                    .forEach(requestContext::addOgImage);
            model.addAttribute("innerImages", innerImages);
        }
        model.addAttribute("topic", posting.getTopic());
        addPostingComments(model, posting, offset, tid);
    }

    void addPostingComments(Model model, Posting posting, Integer offset, Long tid) {
        if (offset == null || tid == null) {
            return;
        }

        offset = forumManager.jumpToComment(posting.getId(), tid, offset, 20);

        model.addAttribute("comments", forumManager.begAll(posting.getId(), offset, 20));
        model.addAttribute("forumForm", new ForumForm(posting, requestContext));
    }

    public LocationInfo generalPostingViewLocationInfo(Posting posting, Model model) {
        LocationInfo generalView = disambiguationController.generalViewLocationInfo(posting, null);
        return new LocationInfo(model)
                .withUri(posting.getGrpDetailsHref())
                .withMenuMain(generalView.getMenuMain())
                .withTopics(generalView.getTopics())
                .withTopicsIndex(generalView.getTopicsIndex())
                .withParent(generalView)
                .withPageTitle(posting.getHeading());
    }

}
