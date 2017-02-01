package ua.org.migdal.controller;

import java.util.Map;

import javax.persistence.PersistenceException;
import javax.validation.Valid;

import org.hibernate.exception.ConstraintViolationException;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.validation.Errors;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.ModelAttribute;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.servlet.mvc.support.RedirectAttributes;
import org.springframework.web.util.UriComponentsBuilder;

import ua.org.migdal.data.User;
import ua.org.migdal.form.SignupForm;
import ua.org.migdal.manager.UsersManager;

@Controller
public class SignupController {

    private Logger log = LoggerFactory.getLogger(SignupController.class);

    @Autowired
    private UsersManager usersManager;

    @GetMapping("/signup")
    public String signup(@RequestParam(required = false) String back, Map<String, Object> model) {
        @SuppressWarnings("unchecked")
        Map<String, Object> menuProps = (Map<String, Object>) model.get("menu");
        menuProps.put("signUp", false);

        model.putIfAbsent("signupForm", new SignupForm(back, "/"));
        return "signup";
    }

    @PostMapping("/signup")
    public String signup(
            @ModelAttribute @Valid SignupForm signupForm,
            Errors errors,
            RedirectAttributes redirectAttributes) {
        if (!errors.hasErrors()) {
            User user = new User();
            signupForm.toUser(user);
            try {
                usersManager.registerUser(user);
            } catch (PersistenceException e) {
                if (e.getCause() instanceof ConstraintViolationException) {
                    ConstraintViolationException cve = (ConstraintViolationException) e.getCause();
                    if ("users_email_idx".equals(cve.getConstraintName())) {
                        errors.rejectValue("email", "used");
                    } else {
                        errors.reject("persistence.failure");
                    }
                } else {
                    errors.reject("persistence.failure");
                }
            }
        }

        if (!errors.hasErrors()) {
            return "redirect:" + signupForm.getBackUrlSafe();
        } else {
            redirectAttributes.addFlashAttribute("errors", errors);
            redirectAttributes.addFlashAttribute("signupForm", signupForm);
            return UriComponentsBuilder.fromUriString("redirect:/signup")
                    .queryParam("back", signupForm.getBackUrlSafe())
                    .toUriString();
        }
    }

}
