package ua.org.migdal.controller;

import javax.inject.Inject;

import org.springframework.data.domain.Sort;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestParam;

import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.data.EntryType;
import ua.org.migdal.data.Posting;
import ua.org.migdal.data.Topic;
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

        model.addAttribute("topic", topic);
        indexController.addPostings("TAPE", topic, null, new String[] {"NEWS", "ARTICLES", "GALLERY", "BOOKS"},
                                    topic.isPostable(), topic.getIdent().equals("migdal"), offset, 20, model);
        model.addAttribute("events", topicManager.begGrandchildren(identManager.idOrIdent("migdal.events")));
        indexController.addSeeAlso(topic.getId(), model);
        indexController.addMajors(model);
        earController.addEars(model);

        return "migdal-news";
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

    @GetMapping("/migdal/jcc")
    public String jcc(Model model) throws PageNotFoundException {
        Posting posting = postingManager.beg(identManager.idOrIdent("post.migdal.jcc"));
        if (posting == null) {
            throw new PageNotFoundException();
        }

        jccLocationInfo(posting, model);

        long jccId = identManager.idOrIdent("migdal.jcc");
        model.addAttribute("jcc", topicManager.beg(jccId));
        Postings p = Postings.all().grp("REVIEWS").topic(jccId).asGuest().sort(Sort.Direction.ASC, "index0");
        model.addAttribute("jccReviews", postingManager.begAll(p));
        addEvents("kaitanot", "kaitanotEvents", "migdal.events.kaitanot", model);
        addEvents("halom", "halomEvents", "migdal.events.halom", model);
        postingViewController.addPostingView(model, posting, null, null);
        indexController.addMajors(model);
        earController.addEars(model);

        return "migdal";
    }

    private void addEvents(String topicVar, String listVar, String ident, Model model) {
        Topic topic = topicManager.beg(identManager.idOrIdent(ident));
        model.addAttribute(topicVar, topic);
        model.addAttribute(listVar, topicManager.begAll(topic.getId(), false, "index0"));
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
    public String jccReorder(Model model) throws PageNotFoundException {
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

    /* TODO
     <elif what='$,Id==$`post.migdal.museum`'>
      <assign name='transref' value#='http://english.$siteDomain/museum/'>
     <elif what='$,Id==$`post.museum,e`'>
      <assign name='transref' value#='http://www.$siteDomain/migdal/museum/'>
     <elif what='$,Id==$`post.migdal.migdal-or`'>
      <assign name='transref' value#='http://english.$siteDomain/migdal-or/'>
     <elif what='$,Id==$`post.migdal-or,e`'>
      <assign name='transref' value#='http://www.$siteDomain/migdal/migdal-or/'>
     <else>
      <assign name='transref' value=''>
     </if>
     */
}
