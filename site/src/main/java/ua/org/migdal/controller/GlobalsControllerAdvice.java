package ua.org.migdal.controller;

import javax.servlet.http.HttpServletRequest;

import javax.inject.Inject;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.ControllerAdvice;
import org.springframework.web.bind.annotation.ModelAttribute;

import ua.org.migdal.Config;
import ua.org.migdal.session.Constants;
import ua.org.migdal.session.RequestContext;
import ua.org.migdal.session.SubdomainUtils;

@ControllerAdvice
public class GlobalsControllerAdvice {

    @Inject
    private Constants constants;

    @Inject
    private RequestContext requestContext;

    @Inject
    private Config config;

    @ModelAttribute
    public void session(HttpServletRequest request, Model model) {
        model.addAttribute("const", constants);
        model.addAttribute("config", config);
        model.addAttribute("rc", requestContext);
        model.addAttribute("location", requestContext.getLocation());
        model.addAttribute("printLocation",
                SubdomainUtils.createLocalBuilderFromRequest(request).queryParam("print", 1).toUriString());
        model.addAttribute("siteDomain", config.getSiteDomain());
        model.addAttribute("siteName", !requestContext.isEnglish() ? "Мигдаль" : "Migdal");
    }

}
