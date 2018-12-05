package ua.org.migdal.controller;

import javax.inject.Inject;

import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;

import ua.org.migdal.manager.TopicManager;
import ua.org.migdal.manager.UserManager;
import ua.org.migdal.session.RequestContext;

/* This controller is used only for redirections from old URLs */

@Controller
public class GalleryController {

    @Inject
    private RequestContext requestContext;

    @Inject
    private TopicManager topicManager;

    @Inject
    private UserManager userManager;

    @GetMapping("/gallery")
    public String gallery() {
        return "redirect:/";
    }

    @GetMapping("/gallery/migdal")
    public String galleryMigdal() {
        return "redirect:/migdal/news/";
    }

    @GetMapping("/gallery/migdal/events")
    public String galleryMigdalEvents() {
        return "redirect:/migdal/events/";
    }

    @GetMapping("/gallery/migdal/events/kaitanot/**")
    public String galleryKaitanot() {
        return "redirect:/migdal/events/kaitanot/" + requestContext.getCatalog(4);
    }

    @GetMapping("/gallery/migdal/events/halom/**")
    public String galleryHalom() {
        return "redirect:/migdal/events/halom/" + requestContext.getCatalog(4);
    }

    @GetMapping("/gallery/taglit")
    public String galleryTaglit() {
        return "redirect:/taglit/";
    }

    @GetMapping("/gallery/veterans")
    public String galleryVeterans() {
        return "redirect:/veterans/";
    }

    @GetMapping("/gallery/{ids}/{user}/")
    public String galleryUser(@PathVariable String ids, @PathVariable String user) {
        try {
            long id = Long.parseLong(ids);
            if (topicManager.exists(id) && userManager.loginExists(user)) {
                return String.format("redirect:/%d/%s/", id, user);
            }
        } catch (NumberFormatException e) {
            /* passthru */
        }
        return galleryGeneral();
    }

    @GetMapping("/gallery/**")
    public String galleryGeneral() {
        return "redirect:/" + requestContext.getCatalog(1) + "gallery/";
    }

}
