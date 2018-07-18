package ua.org.migdal.controller;

import javax.inject.Inject;

import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.RequestParam;
import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.data.Posting;
import ua.org.migdal.form.InnerImageForm;
import ua.org.migdal.location.LocationInfo;
import ua.org.migdal.manager.PostingManager;

@Controller
public class InnerImageController {

    @Inject
    private PostingManager postingManager;

    @Inject
    private PostingViewController postingViewController;

    @GetMapping("/insert-inner-image/{postingId}")
    public String innerImageInsert(
            @PathVariable long postingId,
            @RequestParam int paragraph,
            @RequestParam(defaultValue = "0") int x,
            @RequestParam(defaultValue = "0") int y,
            Model model) throws PageNotFoundException {

        Posting posting = postingManager.beg(postingId);
        if (posting == null) {
            throw new PageNotFoundException();
        }

        innerImageInsertLocationInfo(posting, model);

        model.addAttribute("posting", posting);
        model.asMap().computeIfAbsent("innerImageForm",
                key -> new InnerImageForm(postingId, paragraph, x, y));
        return "inner-image-insert";
    }

    public LocationInfo innerImageInsertLocationInfo(Posting posting, Model model) {
        return new LocationInfo(model)
                .withUri("/insert-inner-image/" + posting.getId())
                .withParent(postingViewController.postingViewLocationInfo(posting, model))
                .withPageTitle(String.format("Вставка картинки в статью \"%s\"", posting.getSubject()));
    }

}
