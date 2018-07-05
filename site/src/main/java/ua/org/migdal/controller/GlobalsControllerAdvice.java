package ua.org.migdal.controller;

import javax.inject.Inject;
import javax.servlet.http.HttpServletRequest;

import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.ControllerAdvice;
import org.springframework.web.bind.annotation.ModelAttribute;

import ua.org.migdal.Config;
import ua.org.migdal.grp.GrpEnum;
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

    @Inject
    private GrpEnum grpEnum;

    @ModelAttribute
    public void session(HttpServletRequest request, Model model) {
        model.addAttribute("const", constants);
        model.addAttribute("config", config);
        model.addAttribute("rc", requestContext);
        model.addAttribute("printLocation",
                SubdomainUtils.createLocalBuilderFromRequest(request)
                        .queryParam("print", 1)
                        .build(true)
                        .toUriString());
        model.addAttribute("siteDomain", config.getSiteDomain());
        model.addAttribute("siteName", !requestContext.isEnglish() ? "Мигдаль" : "Migdal");
        model.addAttribute("grpNone", grpEnum.getGrpNone());
    }

}
