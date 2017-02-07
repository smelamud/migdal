package ua.org.migdal.controller;

import java.util.HashMap;
import java.util.Map;

import org.apache.catalina.servlet4preview.http.HttpServletRequest;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.ControllerAdvice;
import org.springframework.web.bind.annotation.ModelAttribute;

import ua.org.migdal.Config;
import ua.org.migdal.Session;
import ua.org.migdal.util.Utils;

@ControllerAdvice
public class GlobalsControllerAdvice {

    @Autowired
    private Session session;

    @Autowired
    private Config config;

    @Autowired
    private Utils utils;

    @ModelAttribute
    public void session(HttpServletRequest request, Model model) {
        model.addAttribute("session", session);
        model.addAttribute("location", Utils.createLocalBuilderFromRequest(request).toUriString());
        model.addAttribute("printLocation",
                Utils.createLocalBuilderFromRequest(request).queryParam("print", 1).toUriString());
        model.addAttribute("print", "1".equals(request.getParameter("print")));
        String subdomain = getUserDomain(request);
        model.addAttribute("siteDomain", config.getSiteDomain());
        model.addAttribute("userDomain", subdomain);
        model.addAttribute("wwwDomain", subdomain.equals("www"));
        model.addAttribute("englishDomain", subdomain.equals("english"));
        model.addAttribute("siteName", !subdomain.equals("english") ? "Мигдаль" : "Migdal");
        model.addAttribute("pageTitle", "");
        model.addAttribute("metaNoIndex", false);
        model.addAttribute("rssHref", "");
        model.addAttribute("translationHref", "");
        model.addAttribute("menuMain", "index");

        Map<String, Object> props = new HashMap<>();
        props.put("signIn", true);
        props.put("signUp", true);
        model.addAttribute("menu", props);
    }

    private String getUserDomain(HttpServletRequest request) {
        String hostname = Utils.createBuilderFromRequest(request).build().getHost();
        return utils.validateSubdomain(hostname).getSubdomain();
    }

}
