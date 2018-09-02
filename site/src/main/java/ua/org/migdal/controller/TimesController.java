package ua.org.migdal.controller;

import javax.inject.Inject;

import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.data.Posting;
import ua.org.migdal.grp.GrpEnum;
import ua.org.migdal.location.LocationInfo;
import ua.org.migdal.manager.IdentManager;
import ua.org.migdal.manager.PostingManager;

@Controller
public class TimesController {

    @Inject
    private GrpEnum grpEnum;

    @Inject
    private IdentManager identManager;

    @Inject
    private PostingManager postingManager;

    @Inject
    private IndexController indexController;

    @GetMapping("/times/{issue}")
    public String times(@PathVariable int issue, Model model) throws PageNotFoundException {
        long[] grps = new long[] { grpEnum.grpValue("TIMES_COVERS") };
        Posting cover = postingManager.begByIndex1(grps, identManager.idOrIdent("times"), issue);
        if (cover == null) {
            throw new PageNotFoundException();
        }

        timesLocationInfo(cover, model);

        model.addAttribute("issue", issue);
        model.addAttribute("cover", cover);
        model.addAttribute("issues", cover.getIssues());
        return "times";
    }

    public LocationInfo timesLocationInfo(Posting cover, Model model) {
        return new LocationInfo(model)
                .withUri("/times/" + cover.getIndex1())
                .withParent(indexController.indexLocationInfo(null)) //FIXME
                .withMenuMain("times")
                .withPageTitle("Мигдаль Times №" + cover.getIssues())
                .withPageTitleRelative("№" + cover.getIssues());
    }

}
