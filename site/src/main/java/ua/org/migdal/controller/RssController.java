package ua.org.migdal.controller;

import java.util.List;
import java.util.stream.Collectors;

import javax.inject.Inject;

import com.rometools.rome.feed.synd.SyndContent;
import com.rometools.rome.feed.synd.SyndContentImpl;
import com.rometools.rome.feed.synd.SyndEntry;
import com.rometools.rome.feed.synd.SyndEntryImpl;
import com.rometools.rome.io.FeedException;
import org.springframework.util.StringUtils;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.ResponseBody;
import org.springframework.web.bind.annotation.RestController;

import com.rometools.rome.feed.synd.SyndFeed;
import com.rometools.rome.feed.synd.SyndFeedImpl;
import com.rometools.rome.feed.synd.SyndImage;
import com.rometools.rome.feed.synd.SyndImageImpl;
import com.rometools.rome.io.SyndFeedOutput;

import ua.org.migdal.Config;
import ua.org.migdal.data.Posting;
import ua.org.migdal.helper.util.HelperUtils;
import ua.org.migdal.manager.IdentManager;
import ua.org.migdal.manager.PostingManager;
import ua.org.migdal.manager.Postings;
import ua.org.migdal.mtext.MtextConverter;
import ua.org.migdal.session.RequestContext;
import ua.org.migdal.util.Utils;

@RestController
public class RssController {

    @Inject
    private Config config;

    @Inject
    private RequestContext requestContext;

    @Inject
    private IdentManager identManager;

    @Inject
    private PostingManager postingManager;

    @Inject
    private MtextConverter mtextConverter;

    @GetMapping("/rss")
    @ResponseBody
    public String rssMain() throws FeedException {
        if (!requestContext.isEnglish()) {
            return rss(null, "TAPE", "Мигдаль", "Все новости сайта");
        } else {
            return rss("events,e", "TAPE", "Migdal", "All events");
        }
    }

    @GetMapping("/rss/migdal")
    @ResponseBody
    public String rssMigdal() throws FeedException {
        return rss("migdal", "TAPE", "Мигдаль - Новости «Мигдаля»", "Новости «Мигдаля»");
    }

    @GetMapping("/rss/migdal/**")
    public String rssMigdalTopics() {
        return "redirect:/rss/migdal";
    }

    @GetMapping("/rss/times")
    @ResponseBody
    public String rssTimes() throws FeedException {
        return rss(null, "TIMES_COVERS", "Мигдаль - Журнал «Migdal Times»", "Журнал «Migdal Times»");
    }

    @GetMapping("/rss/archive")
    @ResponseBody
    public String rssArchive() throws FeedException {
        return rss(null, "ARCHIVE", "Мигдаль - Архив", "Все, попадающее в архив сайта");
    }

    private String rss(String topicIdent, String grpName, String title, String description) throws FeedException {
        String site = requestContext.getSiteUrl();

        Long topicId = topicIdent != null ? identManager.idOrIdent(topicIdent) : null;
        Postings p = Postings.all().topic(topicId, true).grp(grpName).limit(20).asGuest();
        List<Posting> postings = postingManager.begAllAsList(p);

        SyndFeed feed = new SyndFeedImpl();
        feed.setFeedType("rss_2.0");
        feed.setTitle(title);
        feed.setLink(site + "/");
        feed.setDescription(description);
        feed.setLanguage(!requestContext.isEnglish() ? "ru-ru" : "en-us");
        feed.setWebMaster("webmaster@migdal.org.ua (Shmuel-Leib Melamud)");
        feed.setPublishedDate(!postings.isEmpty() ? postings.get(0).getSent() : Utils.now());
        feed.setGenerator("Migdal website kernel / ROME");

        SyndImage feedImage = new SyndImageImpl();
        feedImage.setUrl(site + "/pics/big-tower.gif");
        feedImage.setTitle(!requestContext.isEnglish() ? "Мигдаль" : "Migdal");
        feedImage.setLink(site + "/");
        feed.setImage(feedImage);

        feed.setEntries(postings.stream().map(this::buildEntry).collect(Collectors.toList()));

        return new SyndFeedOutput().outputString(feed);
    }

    private SyndEntry buildEntry(Posting posting) {
        String site = requestContext.getSiteUrl();

        SyndEntry entry = new SyndEntryImpl();
        entry.setTitle(posting.getHeading());
        entry.setLink(site + posting.getGrpDetailsHref());
        entry.setUri("urn:entry:" + posting.getId());
        entry.setPublishedDate(posting.getSent());
        entry.setComments(entry.getLink() + "#comments");

        StringBuilder buf = new StringBuilder();
        buf.append("<div style=\"display: flex\">");
        if (posting.isHasImage()) {
            buf.append("<div style=\"flex: none; margin-right: 1em\">");
            buf.append("<img");
            HelperUtils.appendAttr(buf, "width", posting.getSmallImageX());
            HelperUtils.appendAttr(buf, "height", posting.getSmallImageY());
            HelperUtils.appendAttr(buf, "src", site + posting.getSmallImageUrl());
            buf.append('>');
            buf.append("</div>");
        }
        buf.append("<div style=\"flex: auto\">");
        buf.append(mtextConverter.toHtml(posting.getBodyMtext()));
        if (!StringUtils.isEmpty(posting.getAuthor())) {
            buf.append("<p><i>");
            buf.append(mtextConverter.toHtml(posting.getAuthorMtext()));
            buf.append("</i></p>");
        }
        if (posting.isHasLargeBody()) {
            buf.append("<p>");
            buf.append("<a");
            HelperUtils.appendAttr(buf, "href", site + posting.getGrpDetailsHref());
            buf.append('>');
            buf.append("&rightarrow; ");
            buf.append(!requestContext.isEnglish() ? "Читать дальше" : "Read further");
            buf.append("</a>");
            buf.append("</p>");
        }
        if (!StringUtils.isEmpty(posting.getUrl())) {
            buf.append("<p>");
            buf.append("<a");
            HelperUtils.appendAttr(buf, "href", posting.getUrl());
            buf.append('>');
            buf.append("&rightarrow; ");
            buf.append("Подробности на другом сайте");
            buf.append("</a>");
            buf.append("</p>");
        }
        buf.append("</div>");
        buf.append("</div>");

        SyndContent content = new SyndContentImpl();
        content.setType("text/html");
        content.setValue(buf.toString());

        entry.setDescription(content);

        return entry;
    }

}
