package ua.org.migdal.controller;

import java.util.HashMap;
import java.util.Map;

import org.apache.catalina.servlet4preview.http.HttpServletRequest;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.ControllerAdvice;
import org.springframework.web.bind.annotation.ModelAttribute;
import org.springframework.web.util.UriComponentsBuilder;

import ua.org.migdal.Session;

@ControllerAdvice
public class GlobalsControllerAdvice {

    @Autowired
    private Session session;

    @ModelAttribute
    public void session(HttpServletRequest request, Model model) {
        model.addAttribute("session", session);
        model.addAttribute("location", UriComponentsBuilder
                .fromPath(request.getRequestURI())
                .query(request.getQueryString())
                .toUriString());

        Map<String, Object> props = new HashMap<>();
        props.put("signIn", true);
        props.put("signUp", true);
        model.addAttribute("menu", props);
    }

}
