package ua.org.migdal.controller;

import java.util.List;

import javax.inject.Inject;

import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.RequestParam;

import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.data.Image;
import ua.org.migdal.data.InnerImage;
import ua.org.migdal.data.Posting;
import ua.org.migdal.form.ForumForm;
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
    private DetailsService detailsService;

    @Inject
    private DisambiguationController disambiguationController;

    @Inject
    private EarController earController;

    @Inject
    private EventController eventController;

    // @GetMapping("/**/{id or ident}")
    public String postingView(
            Model model,
            @RequestParam(defaultValue = "0") Integer offset,
            @RequestParam(defaultValue = "0") Long tid) throws PageNotFoundException {

        long id = identManager.postingIdFromRequestPath();
        Posting posting = postingManager.beg(id);
        if (posting != null) {
            return generalPostingView(posting, model, offset, tid);
        }
        posting = eventController.begDailyNewsPosting(requestContext.getCatalog());
        if (posting != null) {
            return generalPostingView(posting, model, offset, tid);
        }

        throw new PageNotFoundException();
    }

    private String generalPostingView(
            Posting posting,
            Model model,
            Integer offset,
            Long tid) throws PageNotFoundException {

        generalPostingViewLocationInfo(posting, model);

        addPostingView(model, posting, offset, tid);
        earController.addEars(model);

        String mappedTemplate = detailsService.callMapping(posting.getGrpDetailsTemplate(), posting, model);
        return mappedTemplate != null ? mappedTemplate : posting.getGrpDetailsTemplate();
    }

    void addPostingView(Model model, Posting posting, Integer offset, Long tid) {
        if (posting.isHasImage()) {
            requestContext.addOgImage(posting.getImageUrl());
        }

        model.addAttribute("posting", posting);
        crossEntryManager.fetchPublishedEntries(posting);
        if (posting.isGrpInnerImages()) {
            List<InnerImage> innerImages = innerImageManager.getAll(posting.getId());
            innerImages.stream()
                    .map(InnerImage::getImage)
                    .map(Image::getImageUrl)
                    .forEach(requestContext::addOgImage);
            model.addAttribute("innerImages", innerImages);
        }
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
        String topics = posting.getGrpDetailsTopics().equals("parent")
                                ? generalView.getTopics()
                                : posting.getGrpDetailsTopics();
        String topicsIndex;
        switch (posting.getGrpDetailsTopicsIndex()) {
            case "parent":
                topicsIndex = generalView.getTopicsIndex();
                break;
            case "id":
                topicsIndex = Long.toString(posting.getId());
                break;
            case "index1":
                topicsIndex = Long.toString(posting.getIndex1());
                break;
            default:
                topicsIndex = posting.getGrpDetailsTopicsIndex();
        }
        return new LocationInfo(model)
                .withUri(posting.getGrpDetailsHref())
                .withMenuMain(generalView.getMenuMain())
                .withTopics(topics, posting)
                .withTopicsIndex(topicsIndex)
                .withParent(generalView)
                .withPageTitle(posting.getHeading());
    }

}
