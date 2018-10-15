package ua.org.migdal.controller;

import javax.inject.Inject;

import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestParam;
import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.data.Posting;
import ua.org.migdal.data.Topic;
import ua.org.migdal.location.LocationInfo;
import ua.org.migdal.manager.IdentManager;
import ua.org.migdal.manager.PostingManager;
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
        indexController.addSeeAlso(topic.getId(), model);
        indexController.addPostings("TAPE", topic, null, new String[] {"NEWS", "ARTICLES", "GALLERY", "BOOKS"},
                                    topic.isPostable(), topic.getIdent().equals("migdal"), offset, 20, model);
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
