package ua.org.migdal.controller;

import java.util.ArrayList;
import java.util.List;

import javax.inject.Inject;

import org.springframework.data.domain.Sort;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;

import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.data.Posting;
import ua.org.migdal.data.Topic;
import ua.org.migdal.data.util.Siblings;
import ua.org.migdal.grp.GrpEnum;
import ua.org.migdal.location.LocationInfo;
import ua.org.migdal.manager.IdentManager;
import ua.org.migdal.manager.PostingManager;
import ua.org.migdal.manager.TopicManager;

@Controller
public class TimesController {

    @Inject
    private GrpEnum grpEnum;

    @Inject
    private IdentManager identManager;

    @Inject
    private TopicManager topicManager;

    @Inject
    private PostingManager postingManager;

    @Inject
    private IndexController indexController;

    @GetMapping("/times")
    public String times() {
        long[] coverGrps = new long[] { grpEnum.grpValue("TIMES_COVERS") };
        Posting cover = postingManager.begLastByIndex1(coverGrps, 0);
        return "redirect:/times/" + cover.getIndex1();
    }

    public LocationInfo timesLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/times")
                .withParent(indexController.indexLocationInfo(null))
                .withMenuMain("times")
                .withPageTitle("Мигдаль Times");
    }

    @GetMapping("/times/{issue}")
    public String timesIssue(@PathVariable long issue, Model model) throws PageNotFoundException {
        long[] coverGrps = new long[] { grpEnum.grpValue("TIMES_COVERS") };
        long[] articleGrps = new long[] { grpEnum.grpValue("TIMES_ARTICLES") };

        Topic times = topicManager.get(identManager.idOrIdent("times"));
        Posting cover = postingManager.begByIndex1(coverGrps, times.getId(), issue);
        if (cover == null) {
            throw new PageNotFoundException();
        }

        timesIssueLocationInfo(cover, model);

        model.addAttribute("issue", issue);
        model.addAttribute("cover", cover);
        model.addAttribute("issues", cover.getIssues());
        model.addAttribute("editor", times.isPostable());
        Iterable<Posting> allCovers = postingManager.begAll(null, coverGrps, 0, Integer.MAX_VALUE,
                Sort.Direction.DESC, "index1");
        model.addAttribute("allCovers", allCovers);
        model.addAttribute("siblings", siblings(allCovers, 9, issue));
        model.addAttribute("articles", postingManager.begAll(null, articleGrps, issue, null, 0, Integer.MAX_VALUE,
                Sort.Direction.ASC, "index0"));
        return "times";
    }

    public LocationInfo timesIssueLocationInfo(Posting cover, Model model) {
        return new LocationInfo(model)
                .withUri("/times/" + cover.getIndex1())
                .withParent(timesLocationInfo(null))
                .withMenuMain("times")
                .withPageTitle("Мигдаль Times №" + cover.getIssues())
                .withPageTitleRelative("№" + cover.getIssues());
    }

    private Siblings<Posting> siblings(Iterable<Posting> all, int max, long issue) {
        List<Posting> list = new ArrayList<>();
        int i = 0;
        int middle = 0;
        for (Posting posting : all) {
            list.add(posting);
            if (posting.getIndex1() == issue) {
                middle = i;
            }
            i++;
        }

        int start = middle - (max / 2);
        boolean moreBefore = start >= 0;
        start = start < 0 ? 0 : start;
        int end = start + max;
        boolean moreAfter = end <= list.size();
        end = end > list.size() ? list.size() : end;

        return new Siblings<>(new ArrayList<>(list.subList(start, end)), moreBefore, moreAfter);
    }

}
