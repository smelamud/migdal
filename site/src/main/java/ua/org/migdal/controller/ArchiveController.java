package ua.org.migdal.controller;

import java.sql.Timestamp;
import java.time.Duration;
import java.time.LocalDateTime;
import java.time.Month;
import java.time.ZoneOffset;
import java.util.List;

import javax.inject.Inject;

import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;

import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.data.ChatMessage;
import ua.org.migdal.data.util.IntegerRange;
import ua.org.migdal.helper.calendar.Tables;
import ua.org.migdal.location.LocationInfo;
import ua.org.migdal.manager.CachedHtml;
import ua.org.migdal.manager.ChatManager;
import ua.org.migdal.manager.HtmlCacheManager;
import ua.org.migdal.manager.PostingManager;
import ua.org.migdal.manager.Postings;

@Controller
public class ArchiveController {

    @Inject
    private PostingManager postingManager;

    @Inject
    private ChatManager chatManager;

    @Inject
    private HtmlCacheManager htmlCacheManager;

    @Inject
    private IndexController indexController;

    @GetMapping("/archive")
    public String archiveLastYear(Model model) {
        archiveLastYearLocationInfo(model);

        return archive(LocalDateTime.now().getYear(), model);
    }

    public LocationInfo archiveLastYearLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/archive")
                .withRssHref("/rss/archive")
                .withTopics("topics-major")
                .withTopicsIndex("archive")
                .withParent(indexController.indexLocationInfo(null))
                .withPageTitle("Архив");
    }

    @GetMapping("/archive/{year}")
    public String archiveYear(@PathVariable int year, Model model) {
        archiveYearLocationInfo(year, model);

        return archive(year, model);
    }

    public LocationInfo archiveYearLocationInfo(int year, Model model) {
        return new LocationInfo(model)
                .withUri("/archive/" + year)
                .withTopics("topics-major")
                .withTopicsIndex("archive")
                .withParent(archiveLastYearLocationInfo(null))
                .withPageTitle(String.format("Архив, %d г.", year))
                .withPageTitleRelative(String.format("%d г.", year));
    }

    private String archive(int year, Model model) {
        int thisYear = LocalDateTime.now().getYear();
        model.addAttribute("year", year);
        model.addAttribute("years", new IntegerRange(thisYear, 2000));
        CachedHtml archiveCache = htmlCacheManager.of("archive")
                                                  .of(year)
                                                  .during(year == thisYear ? Duration.ofHours(3) : Duration.ofDays(3));
        model.addAttribute("archiveCache", archiveCache);
        if (archiveCache.isInvalid()) {
            Timestamp begin = Timestamp.from(LocalDateTime.of(year, Month.JANUARY, 1, 0, 0).toInstant(ZoneOffset.UTC));
            Timestamp end = Timestamp.from(LocalDateTime.of(year + 1, Month.JANUARY, 1, 0, 0).toInstant(ZoneOffset.UTC));
            Postings p = Postings.all().grp("ARCHIVE").laterThan(begin).earlierThan(end).asGuest();
            model.addAttribute("postings", postingManager.begAll(p));
        }

        return "archive";
    }

    @GetMapping("/chat-archive")
    public String chatArchiveLastYear(Model model) {
        chatArchiveLastYearLocationInfo(model);

        return chatArchive(2007, 7, model);
    }

    public LocationInfo chatArchiveLastYearLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/chat-archive")
                .withTopics("topics-major")
                .withTopicsIndex("chat-archive")
                .withParent(indexController.indexLocationInfo(null))
                .withPageTitle("Архив чата");
    }

    @GetMapping("/chat-archive/{year}")
    public String chatArchiveYear(@PathVariable int year, Model model) throws PageNotFoundException {
        if (year > 2007 || year < 2001) {
            throw new PageNotFoundException();
        }

        chatArchiveYearLocationInfo(year, model);

        return chatArchive(year, 12, model);
    }

    public LocationInfo chatArchiveYearLocationInfo(int year, Model model) {
        return new LocationInfo(model)
                .withUri("/chat-archive/" + year)
                .withTopics("topics-major")
                .withTopicsIndex("chat-archive")
                .withParent(chatArchiveLastYearLocationInfo(null))
                .withPageTitle(String.format("Архив, %d г.", year))
                .withPageTitleRelative(String.format("%d г.", year));
    }

    @GetMapping("/chat-archive/{year}/{month}")
    public String chatArchiveYearMonth(
            @PathVariable int year,
            @PathVariable int month,
            Model model) throws PageNotFoundException {

        if (year > 2007 || year < 2001 || month > 12 || month < 1
                || year == 2001 && month < 11 || year == 2007 && month > 7) {
            throw new PageNotFoundException();
        }

        chatArchiveYearMonthLocationInfo(year, month, model);

        return chatArchive(year, month, model);
    }

    public LocationInfo chatArchiveYearMonthLocationInfo(int year, int month, Model model) {
        String monthName = Tables.GREGORIAN_MONTH_RU_NOM_LC_LONG[month - 1];
        return new LocationInfo(model)
                .withUri("/chat-archive/" + year + '/' + month)
                .withTopics("topics-major")
                .withTopicsIndex("chat-archive")
                .withParent(chatArchiveLastYearLocationInfo(null))
                .withPageTitle(String.format("Архив, %s %d г.", monthName, year))
                .withPageTitleRelative(monthName);
    }

    private String chatArchive(int year, int month, Model model) {
        model.addAttribute("year", year);
        model.addAttribute("years", new IntegerRange(2007, 2000));
        model.addAttribute("month", month);
        CachedHtml chatArchiveCache = htmlCacheManager.of("chatArchive").of(year).of(month);
        model.addAttribute("chatArchiveCache", chatArchiveCache);
        if (chatArchiveCache.isInvalid()) {
            Timestamp begin = Timestamp.from(
                    LocalDateTime.of(year, month, 1, 0, 0, 0).toInstant(ZoneOffset.UTC));
            int lastDayOfMonth = Month.of(month).length(year % 4 == 0);
            Timestamp end = Timestamp.from(
                    LocalDateTime.of(year, month, lastDayOfMonth, 23, 59, 59).toInstant(ZoneOffset.UTC));
            List<ChatMessage> messages = chatManager.getAll(begin, end);
            model.addAttribute("messages", messages);
            model.addAttribute("messagesTotal", ((List) messages).size());
        }

        return "chat-archive";
    }

}
