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
import ua.org.migdal.manager.Postings;
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
        Posting cover = postingManager.begFirst(Postings.all().grp("TIMES_COVERS"));
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
    public String timesIssue(
            @PathVariable long issue,
            Model model,
            @RequestParam(defaultValue = "0") Integer offset,
            @RequestParam(defaultValue = "0") Long tid) throws PageNotFoundException {

        Topic times = topicManager.get(identManager.idOrIdent("times"));
        Posting cover = begCover(issue);

        timesIssueLocationInfo(cover, model);

        model.addAttribute("issue", issue);
        model.addAttribute("cover", cover);
        postingViewController.addPostingComments(model, cover, offset, tid);
        model.addAttribute("issues", cover.getIssues());
        model.addAttribute("editor", times.isPostable());
        Postings p = Postings.all()
                             .grp("TIMES_COVERS")
                             .sort(Sort.Direction.DESC, "index1");
        Iterable<Posting> allCovers = postingManager.begAll(p);
        model.addAttribute("allCovers", allCovers);
        model.addAttribute("siblings", siblings(allCovers, 9, issue));
        p = Postings.all()
                    .grp("TIMES_ARTICLES")
                    .index1(issue)
                    .sort(Sort.Direction.ASC, "index0");
        model.addAttribute("articles", postingManager.begAll(p));
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
        Posting cover = postingManager.begFirst(Postings.all().grp("TIMES_COVERS").index1(issue));
        if (cover == null) {
            throw new PageNotFoundException();
        }
        return cover;
    }

    @DetailsMapping("topics-times")
    protected void timesArticle(Posting posting, Model model) throws PageNotFoundException {
        Posting cover = begCover(posting.getIndex1());
        if (cover == null) {
            throw new PageNotFoundException();
        }

        model.addAttribute("cover", cover);
    }

    @TopicsMapping("topics-times")
    protected void topicsTimes(Posting posting, Model model) {
        try {
            Posting cover = begCover(posting.getIndex1());
            model.addAttribute("cover", cover);

            Postings p = Postings.all()
                    .grp("TIMES_ARTICLES")
                    .index1(posting.getIndex1())
                    .sort(Sort.Direction.ASC, "index0");
            model.addAttribute("allArticles", postingManager.begAll(p));
        } catch (PageNotFoundException e) {
            e.printStackTrace();
        }
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

        Postings p = Postings.all()
                             .grp("TIMES_ARTICLES")
                             .index1(issue)
                             .sort(Sort.Direction.ASC, "index0");
        Iterable<Posting> articles = postingManager.begAll(p);
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
