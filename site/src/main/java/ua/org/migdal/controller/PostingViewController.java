package ua.org.migdal.controller;

import java.util.List;
import javax.inject.Inject;

import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;

import org.springframework.web.bind.annotation.RequestParam;
import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.data.Image;
import ua.org.migdal.data.InnerImage;
import ua.org.migdal.data.Posting;
import ua.org.migdal.form.ForumForm;
import ua.org.migdal.location.GeneralViewFinder;
import ua.org.migdal.location.LocationInfo;
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
    private GeneralViewFinder generalViewFinder;

    @Inject
    private IndexController indexController;

    @Inject
    private EarController earController;

    @GetMapping(path={"/**/{id:\\d+}", "/**/{ident:[^.]+}"})
    public String postingView(
            Model model,
            @RequestParam(defaultValue = "0") Integer offset,
            @RequestParam(defaultValue = "0") Long tid) throws PageNotFoundException {
        long id = identManager.postingIdFromRequestPath();
        Posting posting = postingManager.beg(id);
        if (posting == null) {
            throw new PageNotFoundException();
        }

        postingViewLocationInfo(posting, model);

        if (posting.isHasImage()) {
            requestContext.addOgImage(posting.getImageUrl());
        }

        offset = forumManager.jumpToComment(posting.getId(), tid, offset, 20);

        model.addAttribute("posting", posting);
        if (posting.isGrpInnerImages()) {
            List<InnerImage> innerImages = innerImageManager.getAll(posting.getId());
            innerImages.stream()
                    .map(InnerImage::getImage)
                    .map(Image::getImageUrl)
                    .forEach(requestContext::addOgImage);
            model.addAttribute("innerImages", innerImages);
        }
        model.addAttribute("topic", posting.getTopic());
        model.addAttribute("comments", forumManager.begAll(posting.getId(), offset, 20));
        model.addAttribute("forumForm", new ForumForm(posting, requestContext));
        indexController.addMajors(model);
        earController.addEars(model);

        return posting.getGrpDetailsTemplate();
    }

    public LocationInfo postingViewLocationInfo(Posting posting, Model model) {
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
