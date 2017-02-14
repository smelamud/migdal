package ua.org.migdal.controller;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestParam;

import ua.org.migdal.session.LocationInfo;
import ua.org.migdal.session.Session;
import ua.org.migdal.data.UserRepository;

@Controller
public class LoginController {

    @Autowired
    private Session session;

    @Autowired
    private UserRepository userRepository;

/*
    @GetMapping("/signin")
    public String signin(@RequestParam(required = false) String back, Map<String, Object> model) {
        @SuppressWarnings("unchecked")
        Map<String, Object> menuProps = (Map<String, Object>) model.get("menu");
        menuProps.put("signIn", false);

        if (!session.isLoggedIn()) {
            model.putIfAbsent("loginForm", new LoginForm(back, "/"));
            return "signin";
        } else {
            model.putIfAbsent("logoutForm", new LogoutForm(back, "/"));
            return "signout";
        }
    }
*/

    @GetMapping("/signin")
    public String signin(@RequestParam(required = false) Integer novice, Model model) {
        signinLocationInfo(model);

        model.addAttribute("novice", Integer.toString(novice != null ? novice : 0));
        return "signin";
    }

    public static LocationInfo signinLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/signin")
                .withParent(IndexController.indexLocationInfo(null))
                .withPageTitle("Вход на сайт");
    }
/*
    @PostMapping("/signin")
    public String signin(
            @ModelAttribute @Valid LoginForm loginForm,
            Errors errors,
            RedirectAttributes redirectAttributes) {
        if (!errors.hasErrors()) {
            User user = userRepository.findByEmail(loginForm.getEmail());
            if (user != null && loginForm.getPassword().equals(user.getPassword())) {
                if (user.getRole() != null) {
                    session.setUserId(user.getId());
                    session.setDisplayName(user.getDisplayNameAdministrative());
                } else {
                    errors.reject("banned");
                }
            } else {
                errors.reject("incorrect");
            }
        }

        if (!errors.hasErrors()) {
            return "redirect:" + loginForm.getBackUrlSafe();
        } else {
            redirectAttributes.addFlashAttribute("errors", errors);
            redirectAttributes.addFlashAttribute("loginForm", loginForm);
            return UriComponentsBuilder.fromUriString("redirect:/signin")
                    .queryParam("back", loginForm.getBackUrlSafe())
                    .toUriString();
        }
    }

    @PostMapping("/signout")
    public String signout(@ModelAttribute LogoutForm logoutForm) {
        session.setUserId(0);

        return "redirect:" + logoutForm.getBackUrlSafe();
    }
*/
}
