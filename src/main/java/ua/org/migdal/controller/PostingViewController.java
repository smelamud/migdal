package ua.org.migdal.controller;

import java.lang.reflect.InvocationTargetException;
import java.lang.reflect.Method;
import java.util.List;

import javax.inject.Inject;

import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.util.StringUtils;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.ResponseBody;
import org.springframework.web.util.UriComponentsBuilder;

import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.data.Image;
import ua.org.migdal.data.InnerImage;
import ua.org.migdal.data.Posting;
import ua.org.migdal.form.CommentForm;
import ua.org.migdal.grp.GrpEnum;
import ua.org.migdal.location.LocationInfo;
import ua.org.migdal.manager.CrossEntryManager;
import ua.org.migdal.manager.CommentManager;
import ua.org.migdal.manager.IdentManager;
import ua.org.migdal.manager.InnerImageManager;
import ua.org.migdal.manager.PostingManager;
import ua.org.migdal.mtext.Mtext;
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
    private CommentManager commentManager;

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
            if (requestContext.getCatalog().startsWith("forum/") && posting.getGrp() != grpEnum.grpValue("FORUMS")) {
                return redirectToPosting(posting);
            }
            return generalPostingView(posting, model, offset, tid);
        }
        posting = eventController.begDailyNewsPosting(requestContext.getCatalog());
        if (posting != null) {
            return generalPostingView(posting, model, offset, tid);
        }

        throw new PageNotFoundException();
    }

    private String redirectToPosting(Posting posting) {
        return UriComponentsBuilder.fromUriString(requestContext.getLocation())
                                   .replacePath("redirect:" + posting.getGrpDetailsHref())
                                   .build(true)
                                   .toUriString();
    }

    private String generalPostingView(
            Posting posting,
            Model model,
            Integer offset,
            Long tid) throws PageNotFoundException {

        generalPostingViewLocationInfo(posting, model);

        addPostingView(model, posting, offset, tid);
        earController.addEars(model);

        if (posting.isShadow()) {
            return "posting";
        }
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

        offset = commentManager.jumpTo(posting.getId(), tid, offset, 20);

        model.addAttribute("captchaOnPage", !requestContext.isLogged());
        model.addAttribute("comments", commentManager.begAll(posting.getId(), offset, 20));
        model.addAttribute("commentForm", new CommentForm(posting, requestContext));
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

    @GetMapping("/xml/{id}/{fieldName}")
    @ResponseBody
    public Mtext xml(@PathVariable long id, @PathVariable String fieldName) throws PageNotFoundException {
        Posting posting = postingManager.beg(id);
        if (posting == null) {
            throw new PageNotFoundException();
        }

        String getterName = String.format("get%sMtext", StringUtils.capitalize(fieldName));
        try {
            Method method = posting.getClass().getMethod(getterName);
            if (method == null) {
                throw new PageNotFoundException();
            }
            return (Mtext) method.invoke(posting);
        } catch (NoSuchMethodException | IllegalAccessException | InvocationTargetException e) {
            throw new PageNotFoundException();
        }
    }

}
