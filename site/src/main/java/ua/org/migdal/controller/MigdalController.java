package ua.org.migdal.controller;

import java.sql.Timestamp;

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
import ua.org.migdal.helper.calendar.CalendarType;
import ua.org.migdal.helper.calendar.Formatter;
import ua.org.migdal.location.LocationInfo;
import ua.org.migdal.manager.IdentManager;
import ua.org.migdal.manager.PostingManager;
import ua.org.migdal.manager.Postings;
import ua.org.migdal.manager.TopicManager;

@Controller
public class MigdalController {

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
    private EarController earController;

    @Inject
    private EntryController entryController;

    @GetMapping("/migdal")
    public String migdal(Model model) throws PageNotFoundException {
        Posting posting = postingManager.beg(identManager.idOrIdent("post.migdal"));
        if (posting == null) {
            throw new PageNotFoundException();
        }

        migdalLocationInfo(posting, model);

        postingViewController.addPostingView(model, posting, null, null);
        indexController.addMajors(model);
        earController.addEars(model);

        return "migdal";
    }

    public LocationInfo migdalLocationInfo(Posting posting, Model model) {
        return new LocationInfo(model)
                .withUri("/migdal")
                .withTopics("topics-major")
                .withTopicsIndex("migdal")
                .withParent(indexController.indexLocationInfo(null))
                .withPageTitle(posting.getHeading())
                .withPageTitleRelative("Мигдаль")
                .withPageTitleFull("Мигдаль")
                .withTranslationHref("/");
    }

    public LocationInfo migdalLocationInfo(Model model) {
        Posting posting = postingManager.beg(identManager.idOrIdent("post.migdal"));
        return migdalLocationInfo(posting, model);
    }

    @GetMapping("/migdal/news")
    public String migdalNews(
            @RequestParam(defaultValue = "0") Integer offset,
            Model model) throws PageNotFoundException {

        Topic topic = topicManager.beg(identManager.idOrIdent("migdal"));
        if (topic == null) {
            throw new PageNotFoundException();
        }

        migdalNewsLocationInfo(topic, model);

        model.addAttribute("events", topicManager.begGrandchildren(identManager.idOrIdent("migdal.events")));
        indexController.addMajors(model);
        earController.addEars(model);

        return migdalNews(topic, offset, model);
    }

    public LocationInfo migdalNewsLocationInfo(Topic topic, Model model) {
        return new LocationInfo(model)
                .withUri("/migdal")
                .withTopics("topics-major")
                .withTopicsIndex("migdal.news")
                .withParent(migdalLocationInfo(null))
                .withPageTitle(topic.getSubject())
                .withPageTitleRelative("Новости")
                .withTranslationHref("/events");
    }

    private String migdalNews(Topic topic, Integer offset, Model model) {
        model.addAttribute("topic", topic);
        indexController.addPostings("TAPE", topic, null, new String[] {"NEWS", "ARTICLES", "GALLERY", "BOOKS"},
                topic.isPostable(), topic.getIdent().equals("migdal"), offset, 20, model);
        indexController.addSeeAlso(topic.getId(), model);

        return "migdal-news";
    }

    @GetMapping("/migdal/jcc")
    public String jcc(Model model) throws PageNotFoundException {
        Posting posting = postingManager.beg(identManager.idOrIdent("post.migdal.jcc"));
        if (posting == null) {
            throw new PageNotFoundException();
        }

        jccLocationInfo(posting, model);

        addJcc(model);
        postingViewController.addPostingView(model, posting, null, null);
        earController.addEars(model);

        return "migdal";
    }

    private void addJcc(Model model) {
        long jccId = identManager.idOrIdent("migdal.jcc");
        model.addAttribute("jcc", topicManager.beg(jccId));
        Postings p = Postings.all().grp("REVIEWS").topic(jccId).asGuest().sort(Sort.Direction.ASC, "index0");
        model.addAttribute("jccReviews", postingManager.begAll(p));
        addEvents("kaitanot", "kaitanotEvents", "migdal.events.kaitanot", model);
        addEvents("halom", "halomEvents", "migdal.events.halom", model);
    }

    private void addEvents(String topicVar, String listVar, String ident, Model model) {
        Topic topic = topicManager.beg(identManager.idOrIdent(ident));
        model.addAttribute(topicVar, topic);
        model.addAttribute(listVar, topicManager.begAll(topic.getId(), false, Sort.Direction.DESC, "index2", "index0"));
    }

    public LocationInfo jccLocationInfo(Posting posting, Model model) {
        return new LocationInfo(model)
                .withUri("/migdal/jcc")
                .withTopics("topics-jcc")
                .withTopicsIndex("migdal.jcc")
                .withParent(migdalLocationInfo(null))
                .withPageTitle(posting.getHeading())
                .withPageTitleFull("Мигдаль :: " + posting.getHeading());
    }

    public LocationInfo jccLocationInfo(Model model) {
        Posting posting = postingManager.beg(identManager.idOrIdent("post.migdal.jcc"));
        return jccLocationInfo(posting, model);
    }

    @GetMapping("/migdal/jcc/reorder")
    public String jccReorder(Model model) {
        jccReorderLocationInfo(model);

        Postings p = Postings.all()
                .grp("REVIEWS")
                .topic(identManager.idOrIdent("migdal.jcc"))
                .sort(Sort.Direction.ASC, "index0");
        Iterable<Posting> postings = postingManager.begAll(p);
        return entryController.entryReorder(postings, EntryType.POSTING, model);
    }

    public LocationInfo jccReorderLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/migdal/jcc/reorder")
                .withParent(jccLocationInfo(null))
                .withPageTitle("Расстановка статей");
    }

    @GetMapping("/migdal/jcc/choir")
    public String choir(Model model) throws PageNotFoundException {
        Posting posting = postingManager.beg(identManager.idOrIdent("post.migdal.jcc.choir"));
        if (posting == null) {
            throw new PageNotFoundException();
        }

        choirLocationInfo(posting, model);

        postingViewController.addPostingView(model, posting, null, null);
        earController.addEars(model);
        addHistory(posting.getTopicId(), model);

        return "migdal";
    }

    public LocationInfo choirLocationInfo(Posting posting, Model model) {
        return new LocationInfo(model)
                .withUri("/migdal/jcc/choir")
                .withTopics("topics-choir")
                .withTopicsIndex("migdal.jcc.choir")
                .withParent(jccLocationInfo(null))
                .withPageTitle(posting.getHeading())
                .withPageTitleFull("Мигдаль :: " + posting.getHeading());
    }

    public LocationInfo choirLocationInfo(Model model) {
        Posting posting = postingManager.beg(identManager.idOrIdent("post.migdal.jcc.choir"));
        return choirLocationInfo(posting, model);
    }

    @GetMapping("/migdal/jcc/choir/{id}")
    public String choirHistory(@PathVariable long id, Model model) throws PageNotFoundException {
        Posting posting = postingManager.beg(id);
        if (posting == null) {
            throw new PageNotFoundException();
        }

        choirHistoryLocationInfo(posting, model);

        postingViewController.addPostingView(model, posting, null, null);
        earController.addEars(model);
        addHistory(posting.getTopicId(), model);

        return "migdal";
    }

    public LocationInfo choirHistoryLocationInfo(Posting posting, Model model) {
        String historyDate = historyDate(posting.getSent());
        return new LocationInfo(model)
                .withUri("/migdal/jcc/choir")
                .withTopics("topics-choir")
                .withTopicsIndex(Long.toString(posting.getId()))
                .withParent(choirLocationInfo(null))
                .withPageTitle(String.format("%s (архив от %s)", posting.getHeading(), historyDate))
                .withPageTitleRelative(String.format("Архив от %s", historyDate))
                .withPageTitleFull("Мигдаль :: " + posting.getHeading());
    }

    @GetMapping("/migdal/jcc/dances")
    public String dances(Model model) throws PageNotFoundException {
        Posting posting = postingManager.beg(identManager.idOrIdent("post.migdal.jcc.dances"));
        if (posting == null) {
            throw new PageNotFoundException();
        }

        dancesLocationInfo(posting, model);

        postingViewController.addPostingView(model, posting, null, null);
        earController.addEars(model);
        addHistory(posting.getTopicId(), model);

        return "migdal";
    }

    public LocationInfo dancesLocationInfo(Posting posting, Model model) {
        return new LocationInfo(model)
                .withUri("/migdal/jcc/dances")
                .withTopics("topics-dances")
                .withTopicsIndex("migdal.jcc.dances")
                .withParent(jccLocationInfo(null))
                .withPageTitle(posting.getHeading())
                .withPageTitleFull("Мигдаль :: " + posting.getHeading());
    }

    public LocationInfo dancesLocationInfo(Model model) {
        Posting posting = postingManager.beg(identManager.idOrIdent("post.migdal.jcc.dances"));
        return dancesLocationInfo(posting, model);
    }

    @GetMapping("/migdal/jcc/dances/{id}")
    public String dancesHistory(@PathVariable long id, Model model) throws PageNotFoundException {
        Posting posting = postingManager.beg(id);
        if (posting == null) {
            throw new PageNotFoundException();
        }

        dancesHistoryLocationInfo(posting, model);

        postingViewController.addPostingView(model, posting, null, null);
        earController.addEars(model);
        addHistory(posting.getTopicId(), model);

        return "migdal";
    }

    public LocationInfo dancesHistoryLocationInfo(Posting posting, Model model) {
        String historyDate = historyDate(posting.getSent());
        return new LocationInfo(model)
                .withUri("/migdal/jcc/dances")
                .withTopics("topics-dances")
                .withTopicsIndex(Long.toString(posting.getId()))
                .withParent(dancesLocationInfo(null))
                .withPageTitle(String.format("%s (архив от %s)", posting.getHeading(), historyDate))
                .withPageTitleRelative(String.format("Архив от %s", historyDate))
                .withPageTitleFull("Мигдаль :: " + posting.getHeading());
    }

    private void addHistory(long topicId, Model model) {
        Postings p = Postings.all().grp("REVIEWS").topic(topicId).asGuest();
        model.addAttribute("history", postingManager.begAll(p));
    }

    private String historyDate(Timestamp timestamp) {
        return Formatter.format(CalendarType.GREGORIAN_RU_GEN_LC, "d MMMM yyyy", timestamp.toLocalDateTime());
    }

    @GetMapping("/migdal/jcc/student")
    public String student(Model model) throws PageNotFoundException {
        Posting posting = postingManager.beg(identManager.idOrIdent("post.migdal.jcc.student"));
        if (posting == null) {
            throw new PageNotFoundException();
        }

        studentLocationInfo(posting, model);

        postingViewController.addPostingView(model, posting, null, null);
        earController.addEars(model);
        addStudent(model);

        return "migdal";
    }

    public LocationInfo studentLocationInfo(Posting posting, Model model) {
        return new LocationInfo(model)
                .withUri("/migdal/jcc/student")
                .withTopics("topics-student")
                .withTopicsIndex("migdal.jcc.student")
                .withParent(jccLocationInfo(null))
                .withPageTitle(posting.getHeading())
                .withPageTitleFull("Мигдаль :: " + posting.getHeading());
    }

    public LocationInfo studentLocationInfo(Model model) {
        Posting posting = postingManager.beg(identManager.idOrIdent("post.migdal.jcc.student"));
        return studentLocationInfo(posting, model);
    }

    @GetMapping("/migdal/jcc/student/gallery")
    public String studentGallery(
            @RequestParam(defaultValue = "0") Integer offset,
            @RequestParam(defaultValue = "sent") String sort,
            Model model) throws PageNotFoundException {

        Topic topic = topicManager.beg(identManager.idOrIdent("migdal.jcc.student"));
        if (topic == null) {
            throw new PageNotFoundException();
        }

        studentGalleryLocationInfo(topic, model);

        indexController.addGallery("GALLERY", topic, null, offset, 20, sort, model);
        earController.addEars(model);
        addStudent(model);

        return "gallery";
    }

    public LocationInfo studentGalleryLocationInfo(Topic topic, Model model) {
        return new LocationInfo(model)
                .withUri("/migdal/jcc/student/gallery")
                .withTopics("topics-student")
                .withTopicsIndex("migdal.jcc.student.gallery")
                .withParent(studentLocationInfo(null))
                .withPageTitle(topic.getSubject() + " - Галерея")
                .withPageTitleRelative("Галерея")
                .withPageTitleFull("Мигдаль :: " + topic.getSubject() + " - Галерея");
    }

    private void addStudent(Model model) {
        addEvents("confs", "confsEvents", "migdal.events.youth-confs", model);
        addEvents("kvorim", "kvorimEvents", "migdal.events.kvorim", model);
        addEvents("other", "otherEvents", "migdal.events.other", model);
    }

    @GetMapping("/migdal/jcc/{id}")
    public String jccArticle(@PathVariable String id, Model model) throws PageNotFoundException {
        Posting posting = postingManager.beg(identManager.postingIdFromRequestPath());
        if (posting == null) {
            throw new PageNotFoundException();
        }

        jccArticleLocationInfo(posting, model);

        addJcc(model);
        postingViewController.addPostingView(model, posting, null, null);
        earController.addEars(model);

        return "migdal";
    }

    public LocationInfo jccArticleLocationInfo(Posting posting, Model model) {
        return new LocationInfo(model)
                .withUri(posting.getGrpDetailsHref())
                .withTopics("topics-jcc")
                .withTopicsIndex(Long.toString(posting.getId()))
                .withParent(jccLocationInfo(null))
                .withPageTitle(posting.getHeading())
                .withPageTitleFull("Мигдаль :: " + posting.getHeading());
    }

    @GetMapping("/migdal/library")
    public String library(Model model) throws PageNotFoundException {
        Posting posting = postingManager.beg(identManager.idOrIdent("post.migdal.library"));
        if (posting == null) {
            throw new PageNotFoundException();
        }

        libraryLocationInfo(posting, model);

        addLibrary(model);
        postingViewController.addPostingView(model, posting, null, null);
        earController.addEars(model);
        Postings p = Postings.all()
                             .grp("PRINTINGS")
                             .topic(identManager.idOrIdent("migdal.library"))
                             .limit(10)
                             .asGuest();
        model.addAttribute("printings", postingManager.begAll(p));

        return "migdal-library";
    }

    private void addLibrary(Model model) {
        addEvents("aboker", "abokerEvents", "migdal.events.ad-or-aboker", model);
        addEvents("ofek", "ofekEvents", "migdal.events.ofek", model);
    }

    public LocationInfo libraryLocationInfo(Posting posting, Model model) {
        return new LocationInfo(model)
                .withUri("/migdal/library")
                .withTopics("topics-migdal-library")
                .withTopicsIndex("migdal.library")
                .withParent(migdalLocationInfo(null))
                .withPageTitle(posting.getHeading())
                .withPageTitleFull("Мигдаль :: " + posting.getHeading());
    }

    public LocationInfo libraryLocationInfo(Model model) {
        Posting posting = postingManager.beg(identManager.idOrIdent("post.migdal.library"));
        return libraryLocationInfo(posting, model);
    }

    @GetMapping("/migdal/library/novelties")
    public String libraryNovelties(
            @RequestParam(defaultValue = "0") Integer offset,
            Model model) throws PageNotFoundException {

        Topic topic = topicManager.beg(identManager.idOrIdent("migdal.library"));
        if (topic == null) {
            throw new PageNotFoundException();
        }

        libraryNoveltiesLocationInfo(model);

        addLibrary(model);
        earController.addEars(model);
        indexController.addPostings("PRINTINGS", topic, null, new String[] {"PRINTINGS"}, topic.isPostable(),
                                    offset, 20, model);

        return "migdal-library-novelties";
    }

    public LocationInfo libraryNoveltiesLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/migdal/library/novelties")
                .withTopics("topics-migdal-library")
                .withTopicsIndex("migdal.library.novelties")
                .withParent(libraryLocationInfo(null))
                .withPageTitle("Новинки библиотеки")
                .withPageTitleFull("Мигдаль :: Новинки библиотеки");
    }

    @GetMapping("/migdal/museum")
    public String museum(Model model) throws PageNotFoundException {
        Posting posting = postingManager.beg(identManager.idOrIdent("post.migdal.museum"));
        if (posting == null) {
            throw new PageNotFoundException();
        }

        museumLocationInfo(posting, model);

        addMuseum(posting.getTopicId(), model);
        postingViewController.addPostingView(model, posting, null, null);
        earController.addEars(model);

        return "migdal";
    }

    public LocationInfo museumLocationInfo(Posting posting, Model model) {
        return new LocationInfo(model)
                .withUri("/migdal/museum")
                .withTopics("topics-museum")
                .withTopicsIndex("migdal.museum")
                .withParent(migdalLocationInfo(null))
                .withPageTitle(posting.getHeading())
                .withPageTitleFull("Мигдаль :: " + posting.getHeading())
                .withTranslationHref("/museum");
    }

    public LocationInfo museumLocationInfo(Model model) {
        Posting posting = postingManager.beg(identManager.idOrIdent("post.migdal.museum"));
        return museumLocationInfo(posting, model);
    }

    private void addMuseum(long museumTopicId, Model model) {
        addEvents("confs", "confsEvents", "migdal.events.science-confs", model);
        addHistory(museumTopicId, model);
    }

    void addMuseum(Model model) {
        Topic topic = topicManager.beg(identManager.idOrIdent("migdal.museum"));
        addMuseum(topic.getId(), model);
    }

    @GetMapping("/migdal/museum/news")
    public String museumNews(
            @RequestParam(defaultValue = "0") Integer offset,
            Model model) throws PageNotFoundException {

        Topic topic = topicManager.beg(identManager.idOrIdent("migdal.museum"));
        if (topic == null) {
            throw new PageNotFoundException();
        }

        museumNewsLocationInfo(topic, model);

        addMuseum(topic.getId(), model);
        earController.addEars(model);

        return migdalNews(topic, offset, model);
    }

    public LocationInfo museumNewsLocationInfo(Topic topic, Model model) {
        return new LocationInfo(model)
                .withUri("/migdal/museum/news")
                .withTopics("topics-museum")
                .withTopicsIndex("migdal.museum.news")
                .withParent(museumLocationInfo(null))
                .withPageTitle(topic.getSubject() + " - Новости")
                .withPageTitleRelative("Новости");
    }

    @GetMapping("/migdal/museum/gallery")
    public String museumGallery(
            @RequestParam(defaultValue = "0") Integer offset,
            @RequestParam(defaultValue = "sent") String sort,
            Model model) throws PageNotFoundException {

        Topic topic = topicManager.beg(identManager.idOrIdent("migdal.museum"));
        if (topic == null) {
            throw new PageNotFoundException();
        }

        museumGalleryLocationInfo(topic, model);

        indexController.addGallery("GALLERY", topic, null, offset, 20, sort, model);
        earController.addEars(model);
        addMuseum(topic.getId(), model);

        return "gallery";
    }

    public LocationInfo museumGalleryLocationInfo(Topic topic, Model model) {
        return new LocationInfo(model)
                .withUri("/migdal/museum/gallery")
                .withTopics("topics-museum")
                .withTopicsIndex("migdal.museum.gallery")
                .withParent(museumLocationInfo(null))
                .withPageTitle(topic.getSubject() + " - Галерея")
                .withPageTitleRelative("Галерея")
                .withPageTitleFull("Мигдаль :: " + topic.getSubject() + " - Галерея");
    }

    @GetMapping("/migdal/migdal-or")
    public String migdalOr(Model model) throws PageNotFoundException {
        Posting posting = postingManager.beg(identManager.idOrIdent("post.migdal.migdal-or"));
        if (posting == null) {
            throw new PageNotFoundException();
        }

        migdalOrLocationInfo(posting, model);

        addMigdalOr(posting.getTopicId(), model);
        postingViewController.addPostingView(model, posting, null, null);
        earController.addEars(model);

        return "migdal";
    }

    public LocationInfo migdalOrLocationInfo(Posting posting, Model model) {
        return new LocationInfo(model)
                .withUri("/migdal/migdal-or")
                .withTopics("topics-migdal-or")
                .withTopicsIndex("migdal.migdal-or")
                .withParent(migdalLocationInfo(null))
                .withPageTitle(posting.getHeading())
                .withPageTitleFull("Мигдаль :: " + posting.getHeading())
                .withTranslationHref("/migdal-or");
    }

    public LocationInfo migdalOrLocationInfo(Model model) {
        Posting posting = postingManager.beg(identManager.idOrIdent("post.migdal.migdal-or"));
        return migdalOrLocationInfo(posting, model);
    }

    private void addMigdalOr(long migdalOrTopicId, Model model) {
        addEvents("tours", "toursEvents", "migdal.events.migdal-or-tours", model);
    }

    @GetMapping("/migdal/migdal-or/gallery")
    public String migdalOrGallery(
            @RequestParam(defaultValue = "0") Integer offset,
            @RequestParam(defaultValue = "sent") String sort,
            Model model) throws PageNotFoundException {

        Topic topic = topicManager.beg(identManager.idOrIdent("migdal.migdal-or"));
        if (topic == null) {
            throw new PageNotFoundException();
        }

        migdalOrGalleryLocationInfo(topic, model);

        indexController.addGallery("GALLERY", topic, null, offset, 20, sort, model);
        earController.addEars(model);
        addMigdalOr(topic.getId(), model);

        return "gallery";
    }

    public LocationInfo migdalOrGalleryLocationInfo(Topic topic, Model model) {
        return new LocationInfo(model)
                .withUri("/migdal/migdal-or/gallery")
                .withTopics("topics-migdal-or")
                .withTopicsIndex("migdal.migdal-or.gallery")
                .withParent(migdalOrLocationInfo(null))
                .withPageTitle(topic.getSubject() + " - Галерея")
                .withPageTitleRelative("Галерея")
                .withPageTitleFull("Мигдаль :: " + topic.getSubject() + " - Галерея");
    }

    @GetMapping("/migdal/mazltov")
    public String mazltov(Model model) throws PageNotFoundException {
        Posting posting = postingManager.beg(identManager.idOrIdent("post.migdal.mazltov"));
        if (posting == null) {
            throw new PageNotFoundException();
        }

        mazltovLocationInfo(posting, model);

        addMazltov(posting.getTopicId(), model);
        postingViewController.addPostingView(model, posting, null, null);
        earController.addEars(model);

        return "migdal";
    }

    public LocationInfo mazltovLocationInfo(Posting posting, Model model) {
        return new LocationInfo(model)
                .withUri("/migdal/mazltov")
                .withTopics("topics-mazltov")
                .withTopicsIndex("migdal.mazltov")
                .withParent(migdalLocationInfo(null))
                .withPageTitle(posting.getHeading())
                .withPageTitleFull("Мигдаль :: " + posting.getHeading());
    }

    public LocationInfo mazltovLocationInfo(Model model) {
        Posting posting = postingManager.beg(identManager.idOrIdent("post.migdal.mazltov"));
        return mazltovLocationInfo(posting, model);
    }

    private void addMazltov(long mazltovTopicId, Model model) {
        addEvents("birth", "birthEvents", "migdal.events.mazltov-birth", model);
    }

    @GetMapping("/migdal/mazltov/news")
    public String mazltovNews(
            @RequestParam(defaultValue = "0") Integer offset,
            Model model) throws PageNotFoundException {

        Topic topic = topicManager.beg(identManager.idOrIdent("migdal.mazltov"));
        if (topic == null) {
            throw new PageNotFoundException();
        }

        mazltovNewsLocationInfo(topic, model);

        addMazltov(topic.getId(), model);
        earController.addEars(model);

        return migdalNews(topic, offset, model);
    }

    public LocationInfo mazltovNewsLocationInfo(Topic topic, Model model) {
        return new LocationInfo(model)
                .withUri("/migdal/mazltov/news")
                .withTopics("topics-mazltov")
                .withTopicsIndex("migdal.mazltov.news")
                .withParent(mazltovLocationInfo(null))
                .withPageTitle(topic.getSubject() + " - Новости")
                .withPageTitleRelative("Новости");
    }

    @GetMapping("/migdal/mazltov/gallery")
    public String mazltovGallery(
            @RequestParam(defaultValue = "0") Integer offset,
            @RequestParam(defaultValue = "sent") String sort,
            Model model) throws PageNotFoundException {

        Topic topic = topicManager.beg(identManager.idOrIdent("migdal.mazltov"));
        if (topic == null) {
            throw new PageNotFoundException();
        }

        mazltovGalleryLocationInfo(topic, model);

        indexController.addGallery("GALLERY", topic, null, offset, 20, sort, model);
        earController.addEars(model);
        addMazltov(topic.getId(), model);

        return "gallery";
    }

    public LocationInfo mazltovGalleryLocationInfo(Topic topic, Model model) {
        return new LocationInfo(model)
                .withUri("/migdal/mazltov/gallery")
                .withTopics("topics-mazltov")
                .withTopicsIndex("migdal.mazltov.gallery")
                .withParent(mazltovLocationInfo(null))
                .withPageTitle(topic.getSubject() + " - Галерея")
                .withPageTitleRelative("Галерея")
                .withPageTitleFull("Мигдаль :: " + topic.getSubject() + " - Галерея");
    }

    @GetMapping("/migdal/mazltov/funny-stories")
    public String mazltovFunnyStories(Model model) throws PageNotFoundException {
        Posting posting = postingManager.beg(identManager.idOrIdent("post.migdal.mazltov.funny-stories"));
        if (posting == null) {
            throw new PageNotFoundException();
        }

        mazltovFunnyStoriesLocationInfo(posting, model);

        addMazltov(posting.getTopicId(), model);
        postingViewController.addPostingView(model, posting, null, null);
        earController.addEars(model);

        return "migdal";
    }

    public LocationInfo mazltovFunnyStoriesLocationInfo(Posting posting, Model model) {
        return new LocationInfo(model)
                .withUri("/migdal/mazltov/funny-stories")
                .withTopics("topics-mazltov")
                .withTopicsIndex("migdal.mazltov.funny-stories")
                .withParent(mazltovLocationInfo(null))
                .withPageTitle(posting.getHeading())
                .withPageTitleFull("Мигдаль :: " + posting.getHeading());
    }

    @GetMapping("/migdal/beitenu")
    public String beitenu(Model model) throws PageNotFoundException {
        Posting posting = postingManager.beg(identManager.idOrIdent("post.migdal.beitenu"));
        if (posting == null) {
            throw new PageNotFoundException();
        }

        beitenuLocationInfo(posting, model);

        postingViewController.addPostingView(model, posting, null, null);
        earController.addEars(model);

        return "migdal";
    }

    public LocationInfo beitenuLocationInfo(Posting posting, Model model) {
        return new LocationInfo(model)
                .withUri("/migdal/beitenu")
                .withTopics("topics-beitenu")
                .withTopicsIndex("migdal.beitenu")
                .withParent(migdalLocationInfo(null))
                .withPageTitle(posting.getHeading())
                .withPageTitleFull("Мигдаль :: " + posting.getHeading());
    }

    public LocationInfo beitenuLocationInfo(Model model) {
        Posting posting = postingManager.beg(identManager.idOrIdent("post.migdal.beitenu"));
        return beitenuLocationInfo(posting, model);
    }

    @GetMapping("/migdal/beitenu/news")
    public String beitenuNews(
            @RequestParam(defaultValue = "0") Integer offset,
            Model model) throws PageNotFoundException {

        Topic topic = topicManager.beg(identManager.idOrIdent("migdal.beitenu"));
        if (topic == null) {
            throw new PageNotFoundException();
        }

        beitenuNewsLocationInfo(topic, model);

        earController.addEars(model);

        return migdalNews(topic, offset, model);
    }

    public LocationInfo beitenuNewsLocationInfo(Topic topic, Model model) {
        return new LocationInfo(model)
                .withUri("/migdal/beitenu/news")
                .withTopics("topics-beitenu")
                .withTopicsIndex("migdal.beitenu.news")
                .withParent(beitenuLocationInfo(null))
                .withPageTitle(topic.getSubject() + " - Новости")
                .withPageTitleRelative("Новости");
    }

    @GetMapping("/migdal/beitenu/gallery")
    public String beitenuGallery(
            @RequestParam(defaultValue = "0") Integer offset,
            @RequestParam(defaultValue = "sent") String sort,
            Model model) throws PageNotFoundException {

        Topic topic = topicManager.beg(identManager.idOrIdent("migdal.beitenu"));
        if (topic == null) {
            throw new PageNotFoundException();
        }

        beitenuGalleryLocationInfo(topic, model);

        indexController.addGallery("GALLERY", topic, null, offset, 20, sort, model);
        earController.addEars(model);

        return "gallery";
    }

    public LocationInfo beitenuGalleryLocationInfo(Topic topic, Model model) {
        return new LocationInfo(model)
                .withUri("/migdal/beitenu/gallery")
                .withTopics("topics-beitenu")
                .withTopicsIndex("migdal.beitenu.gallery")
                .withParent(beitenuLocationInfo(null))
                .withPageTitle(topic.getSubject() + " - Галерея")
                .withPageTitleRelative("Галерея")
                .withPageTitleFull("Мигдаль :: " + topic.getSubject() + " - Галерея");
    }

    @GetMapping("/migdal/methodology")
    public String methodology(Model model) throws PageNotFoundException {
        Posting posting = postingManager.beg(identManager.idOrIdent("post.migdal.methodology"));
        if (posting == null) {
            throw new PageNotFoundException();
        }

        methodologyLocationInfo(posting, model);

        addMethodology(model);
        indexController.addSeeAlso(posting.getTopicId(), model);
        postingViewController.addPostingView(model, posting, null, null);
        earController.addEars(model);
        Postings p = Postings.all()
                .grp("BOOKS")
                .topic(identManager.idOrIdent("migdal.methodology"))
                .asGuest();
        model.addAttribute("books", postingManager.begAll(p));

        return "methodic-center";
    }

    private void addMethodology(Model model) {
        addEvents("aboker", "abokerEvents", "migdal.events.ad-or-aboker", model);
        addEvents("halom", "halomEvents", "migdal.events.halom", model);
        addEvents("youth", "youthEvents", "migdal.events.youth-confs", model);
        addEvents("science", "scienceEvents", "migdal.events.science-confs", model);
    }

    public LocationInfo methodologyLocationInfo(Posting posting, Model model) {
        return new LocationInfo(model)
                .withUri("/migdal/methodology")
                .withTopics("topics-methodology")
                .withTopicsIndex("migdal.methodology")
                .withParent(migdalLocationInfo(null))
                .withPageTitle(posting.getHeading())
                .withPageTitleRelative("Методический центр")
                .withPageTitleFull("Мигдаль :: " + posting.getHeading());
    }

    public LocationInfo methodologyLocationInfo(Model model) {
        Posting posting = postingManager.beg(identManager.idOrIdent("post.migdal.methodology"));
        return methodologyLocationInfo(posting, model);
    }

    @GetMapping("/migdal/methodology/books")
    public String methodologyBooks(Model model) throws PageNotFoundException {

        Topic topic = topicManager.beg(identManager.idOrIdent("migdal.methodology"));
        if (topic == null) {
            throw new PageNotFoundException();
        }

        methodologyBooksLocationInfo(model);

        addMethodology(model);
        earController.addEars(model);
        indexController.addPostings("BOOKS", topic, null, new String[] {"BOOKS"}, topic.isPostable(),
                0, Integer.MAX_VALUE, model);

        return "migdal-news";
    }

    public LocationInfo methodologyBooksLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/migdal/methodology/books")
                .withTopics("topics-methodology")
                .withTopicsIndex("migdal.methodology.books")
                .withParent(methodologyLocationInfo(null))
                .withPageTitle("Методические пособия")
                .withPageTitleFull("Мигдаль :: Методические пособия");
    }

    @GetMapping("/migdal/printings")
    public String printings(Model model) throws PageNotFoundException {
        Topic topic = topicManager.beg(identManager.idOrIdent("migdal.printings"));
        if (topic == null) {
            throw new PageNotFoundException();
        }

        printingsLocationInfo(model);

        model.addAttribute("printings", topic);
        indexController.addMajors(model);
        earController.addEars(model);
        indexController.addSeeAlso(topic.getId(), model);
        Postings p = Postings.all()
                .topic(topic.getId(), true)
                .grp("PRINTINGS")
                .sort(Sort.Direction.ASC, "parent.index0", "index0");
        model.addAttribute("postings", postingManager.begAll(p));

        return "printings";
    }

    public LocationInfo printingsLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/migdal/printings")
                .withTopics("topics-major")
                .withTopicsIndex("migdal.printings")
                .withParent(migdalLocationInfo(null))
                .withPageTitle("Издательский центр")
                .withPageTitleFull("Мигдаль :: Издательский центр");
    }

    @GetMapping("/migdal/printings/reorder")
    public String printingsReorder(Model model) {
        printingsReorderLocationInfo(model);

        Iterable<Topic> topics = topicManager.begAll(
                identManager.idOrIdent("migdal.printings"), false,
                Sort.Direction.ASC, "index0");
        return entryController.entryReorder(topics, EntryType.TOPIC, model);
    }

    public LocationInfo printingsReorderLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/migdal/printings/reorder")
                .withParent(printingsLocationInfo(null))
                .withPageTitle("Расстановка подразделов");
    }

    @GetMapping("/migdal/printings/{id}/reorder")
    public String printingsBooksReorder(Model model) throws PageNotFoundException {
        Topic topic = topicManager.beg(identManager.topicIdFromRequestPath(0, -1));
        if (topic == null) {
            throw new PageNotFoundException();
        }

        printingsBooksReorderLocationInfo(topic, model);

        Postings p = Postings.all()
                .topic(topic.getId())
                .grp("PRINTINGS")
                .sort(Sort.Direction.ASC, "index0");
        Iterable<Posting> postings = postingManager.begAll(p);
        return entryController.entryReorder(postings, EntryType.POSTING, model);
    }

    public LocationInfo printingsBooksReorderLocationInfo(Topic topic, Model model) {
        return new LocationInfo(model)
                .withUri("/" + topic.getCatalog() + "reorder")
                .withParent(printingsLocationInfo(null))
                .withPageTitle("Расстановка книг");
    }

    /* TODO
     <elif what='$,Id==$`post.museum,e`'>
      <assign name='transref' value#='http://www.$siteDomain/migdal/museum/'>
     <elif what='$,Id==$`post.migdal-or,e`'>
      <assign name='transref' value#='http://www.$siteDomain/migdal/migdal-or/'>
     */
}
