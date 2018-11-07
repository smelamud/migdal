package ua.org.migdal.controller;

import java.sql.Timestamp;
import java.time.LocalDateTime;
import java.time.Month;
import java.time.ZoneOffset;

import javax.inject.Inject;

import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;

import org.springframework.web.bind.annotation.PathVariable;
import ua.org.migdal.data.util.IntegerRange;
import ua.org.migdal.location.LocationInfo;
import ua.org.migdal.manager.IdentManager;
import ua.org.migdal.manager.PostingManager;
import ua.org.migdal.manager.Postings;

@Controller
public class ArchiveController {

    @Inject
    private IdentManager identManager;

    @Inject
    private PostingManager postingManager;

    @Inject
    private IndexController indexController;

    @GetMapping("/archive")
    public String archive(Model model) {
        archiveLocationInfo(model);

        return archiveYear(LocalDateTime.now().getYear(), model);
    }

    public LocationInfo archiveLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/archive")
                .withTopics("topics-major")
                .withTopicsIndex("archive")
                .withParent(indexController.indexLocationInfo(null))
                .withPageTitle("Архив");
    }

    @GetMapping("/archive/{year}")
    public String archiveOlder(@PathVariable int year, Model model) {
        archiveOlderLocationInfo(year, model);

        return archiveYear(year, model);
    }

    public LocationInfo archiveOlderLocationInfo(int year, Model model) {
        return new LocationInfo(model)
                .withUri("/archive/" + year)
                .withTopics("topics-major")
                .withTopicsIndex("archive")
                .withParent(archiveLocationInfo(null))
                .withPageTitle(String.format("Архив, %d г.", year))
                .withPageTitleRelative(String.format("%d г.", year));
    }

    private String archiveYear(int year, Model model) {
        model.addAttribute("year", year);
        model.addAttribute("years", new IntegerRange(LocalDateTime.now().getYear(), 2000));
        Timestamp begin = Timestamp.from(LocalDateTime.of(year, Month.JANUARY, 1, 0, 0).toInstant(ZoneOffset.UTC));
        Timestamp end = Timestamp.from(LocalDateTime.of(year + 1, Month.JANUARY, 1, 0, 0).toInstant(ZoneOffset.UTC));
        Postings p = Postings.all().grp("ARCHIVE").laterThan(begin).earlierThan(end).asGuest();
        model.addAttribute("postings", postingManager.begAll(p));

        return "archive";
    }

}
