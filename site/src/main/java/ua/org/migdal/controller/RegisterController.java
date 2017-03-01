package ua.org.migdal.controller;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;

import org.springframework.web.bind.annotation.GetMapping;
import ua.org.migdal.form.UserForm;
import ua.org.migdal.session.LocationInfo;

@Controller
public class RegisterController {

    @Autowired
    private IndexController indexController;

    @GetMapping("/register")
    public String register(Model model) {
        registerLocationInfo(model);

        model.asMap().putIfAbsent("userForm", new UserForm());
        return "register";
    }

    public LocationInfo registerLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/register")
                .withParent(indexController.indexLocationInfo(null))
                .withPageTitle("Регистрация пользователя");
    }

}
