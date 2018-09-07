package ua.org.migdal.controller;

import java.util.ArrayList;
import java.util.List;

import javax.inject.Inject;

import org.springframework.data.domain.Sort;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;

import org.springframework.web.bind.annotation.RequestParam;
import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.data.EntryType;
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

    @Inject
    private PostingViewController postingViewController;

    @Inject
    private PostingEditingController postingEditingController;

    @Inject
    private EntryController entryController;

    @Inject
    private EarController earController;

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

    private Posting begCover(long issue) throws PageNotFoundException {
        long[] coverGrps = new long[]{ grpEnum.grpValue("TIMES_COVERS") };
        Posting cover = postingManager.begByIndex1(coverGrps, 0, issue);
        if (cover == null) {
            throw new PageNotFoundException();
        }
        return cover;
    }

    @GetMapping("/times/{issue}/{id}")
    public String timesArticle(
            @PathVariable long issue,
            Model model,
            @RequestParam(defaultValue = "0") Integer offset,
            @RequestParam(defaultValue = "0") Long tid) throws PageNotFoundException {

        long id = identManager.postingIdFromRequestPath();
        Posting posting = postingManager.beg(id);
        if (posting == null) {
            throw new PageNotFoundException();
        }
        if (posting.getGrp() != grpEnum.grpValue("TIMES_ARTICLES") || posting.getIndex1() != issue) {
            throw new PageNotFoundException();
        }
        Posting cover = begCover(issue);

        timesArticleLocationInfo(cover, posting, model);

        postingViewController.addPostingView(model, posting, offset, tid);
        model.addAttribute("cover", cover);
        long[] articleGrps = new long[] { grpEnum.grpValue("TIMES_ARTICLES") };
        model.addAttribute("allArticles", postingManager.begAll(null, articleGrps, issue, null, 0, Integer.MAX_VALUE,
                Sort.Direction.ASC, "index0"));
        earController.addEars(model);

        return "article-times";
    }

    public LocationInfo timesArticleLocationInfo(Posting cover, Posting posting, Model model) {
        return new LocationInfo(model)
                .withUri(String.format("/times/%d/%d", posting.getIndex1(), posting.getId()))
                .withTopics("topics-times")
                .withTopicsIndex(Long.toString(posting.getId()))
                .withParent(timesIssueLocationInfo(cover, null))
                .withMenuMain("times")
                .withPageTitle(posting.getHeading());
    }

    @GetMapping("/times/add")
    public String timesIssueAdd(@RequestParam(required = false) boolean full, Model model)
            throws PageNotFoundException {

        timesIssueAddLocationInfo(model);

        return postingEditingController.postingAdd(
                "TIMES_COVERS",
                identManager.idOrIdent("times"),
                null,
                full,
                model);
    }

    public LocationInfo timesIssueAddLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/times/add")
                .withParent(timesLocationInfo(null))
                .withPageTitle("Мигдаль Times - Добавление номера")
                .withPageTitleRelative("Добавление номера");
    }

    @GetMapping("/times/{issue}/edit")
    public String timesIssueEdit(
            @PathVariable long issue,
            @RequestParam(required = false) boolean full,
            Model model) throws PageNotFoundException {

        Posting cover = begCover(issue);

        timesIssueEditLocationInfo(issue, model);

        return postingEditingController.postingAddOrEdit(
                cover.getId(),
                "TIMES_COVERS",
                identManager.idOrIdent("times"),
                null,
                full,
                model);
    }

    public LocationInfo timesIssueEditLocationInfo(long issue, Model model) {
        return new LocationInfo(model)
                .withUri(String.format("/times/%d/edit", issue))
                .withParent(timesLocationInfo(null))
                .withPageTitle("Редактирование номера");
    }

    @GetMapping("/times/{issue}/add")
    public String timesArticleAdd(
            @PathVariable long issue,
            @RequestParam(required = false) boolean full,
            Model model) throws PageNotFoundException {

        timesArticleAddLocationInfo(begCover(issue), model);

        return postingEditingController.postingAdd(
                "TIMES_ARTICLES",
                identManager.idOrIdent("times"),
                p -> {
                    p.setIndex1(issue);
                    return p;
                },
                full,
                model);
    }

    public LocationInfo timesArticleAddLocationInfo(Posting cover, Model model) {
        return new LocationInfo(model)
                .withUri("/times/add")
                .withParent(timesIssueLocationInfo(cover, null))
                .withPageTitle("Мигдаль Times №" + cover.getIndex1() + " - Добавление статьи")
                .withPageTitleRelative("Добавление статьи");
    }

    @GetMapping("/times/{issue}/reorder")
    public String timesArticlesReorder(@PathVariable long issue, Model model) throws PageNotFoundException {
        timesArticlesReorderLocationInfo(begCover(issue), model);

        long[] articleGrps = new long[] { grpEnum.grpValue("TIMES_ARTICLES") };
        Iterable<Posting> articles = postingManager.begAll(null, articleGrps, issue, null, 0, Integer.MAX_VALUE,
                Sort.Direction.ASC, "index0");
        return entryController.entryReorder(articles, EntryType.POSTING, model);
    }

    public LocationInfo timesArticlesReorderLocationInfo(Posting cover, Model model) {
        return new LocationInfo(model)
                .withUri(String.format("/times/%d/reorder", cover.getIndex1()))
                .withParent(timesIssueLocationInfo(cover, null))
                .withPageTitle("Мигдаль Times №" + cover.getIndex1() + " - Расстановка статей")
                .withPageTitleRelative("Расстановка статей");
    }

}
