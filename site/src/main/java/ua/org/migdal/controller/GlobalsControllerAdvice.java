package ua.org.migdal.controller;

import java.util.HashMap;
import java.util.Map;

import org.apache.catalina.servlet4preview.http.HttpServletRequest;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.ControllerAdvice;
import org.springframework.web.bind.annotation.ModelAttribute;

import ua.org.migdal.Session;
import ua.org.migdal.util.Utils;

@ControllerAdvice
public class GlobalsControllerAdvice {

    @Autowired
    private Session session;

    @ModelAttribute
    public void session(HttpServletRequest request, Model model) {
        model.addAttribute("session", session);
        model.addAttribute("location", Utils.createLocalBuilderFromRequest(request).toUriString());

        Map<String, Object> props = new HashMap<>();
        props.put("signIn", true);
        props.put("signUp", true);
        model.addAttribute("menu", props);
    }

}
