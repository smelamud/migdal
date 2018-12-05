package ua.org.migdal.controller;

import javax.inject.Inject;

import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import ua.org.migdal.manager.PostingManager;

/* This controller is used only for redirections from old URLs */

@Controller
public class LinksController {

    @Inject
    private PostingManager postingManager;

    @GetMapping(path = { "/links", "/links/urls", "/links/{topic}" })
    public String links() {
        return "redirect:/internet/";
    }

    @GetMapping("/links/**/{ids}")
    public String linksPosting(@PathVariable String ids) {
        try {
            long id = Long.parseLong(ids);
            if (postingManager.exists(id)) {
                return String.format("redirect:/internet/%d/", id);
            }
        } catch (NumberFormatException e) {
            /* passthru */
        }
        return "redirect:/internet/";
    }

}
