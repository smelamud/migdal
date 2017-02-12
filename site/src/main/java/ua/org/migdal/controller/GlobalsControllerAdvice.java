package ua.org.migdal.controller;

import javax.servlet.http.HttpServletRequest;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.ControllerAdvice;
import org.springframework.web.bind.annotation.ModelAttribute;

import ua.org.migdal.Config;
import ua.org.migdal.RequestContext;
import ua.org.migdal.util.Utils;

@ControllerAdvice
public class GlobalsControllerAdvice {

    @Autowired
    private RequestContext requestContext;

    @Autowired
    private Config config;

    @ModelAttribute
    public void session(HttpServletRequest request, Model model) {
        model.addAttribute("rc", requestContext);
        model.addAttribute("location", Utils.createLocalBuilderFromRequest(request).toUriString());
        model.addAttribute("printLocation",
                Utils.createLocalBuilderFromRequest(request).queryParam("print", 1).toUriString());
        model.addAttribute("siteDomain", config.getSiteDomain());
        model.addAttribute("userDomain", requestContext.getSubdomain());
        model.addAttribute("siteName", !requestContext.isEnglish() ? "Мигдаль" : "Migdal");
        model.addAttribute("pageTitle", "");
        model.addAttribute("metaNoIndex", false);
        model.addAttribute("rssHref", "");
        model.addAttribute("translationHref", "");
        model.addAttribute("menuMain", "index");
    }

}
