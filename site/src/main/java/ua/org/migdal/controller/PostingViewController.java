package ua.org.migdal.controller;

import javax.inject.Inject;

import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.data.Posting;
import ua.org.migdal.manager.IdentManager;
import ua.org.migdal.manager.PostingManager;
import ua.org.migdal.session.LocationInfo;
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
    private IndexController indexController;

    @GetMapping(path={"/**/{id:\\d+}", "/**/{ident:[^.]+}"})
    public String postingView(Model model) throws PageNotFoundException {
        long id = identManager.postingIdFromRequestPath();
        Posting posting = postingManager.beg(id);
        if (posting == null) {
            throw new PageNotFoundException();
        }

        postingViewLocationInfo(posting, model);

        if (posting.isHasImage()) {
            requestContext.addOgImage(posting.getImageUrl());
        }

        model.addAttribute("posting", posting);

        return "posting";
    }

    public LocationInfo postingViewLocationInfo(Posting posting, Model model) {
        String menuMain = posting.getCatalog().startsWith("times/") ? "times" : "index";
        return new LocationInfo(model)
                .withUri(posting.getGrpDetailsHref())
                .withMenuMain(menuMain)
                .withTopics("topics-major")
                .withTopicsIndex("index")
                .withParent(indexController.indexLocationInfo(null)) // FIXME need somehow to get topic's location info
                .withPageTitle(posting.getHeading());
    }

}
