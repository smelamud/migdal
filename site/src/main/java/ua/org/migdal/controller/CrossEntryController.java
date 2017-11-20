package ua.org.migdal.controller;

import javax.inject.Inject;
import javax.validation.Valid;

import org.springframework.stereotype.Controller;
import org.springframework.transaction.PlatformTransactionManager;
import org.springframework.ui.Model;
import org.springframework.validation.Errors;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.ModelAttribute;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.servlet.mvc.support.RedirectAttributes;

import ua.org.migdal.form.CrossEntryAddForm;
import ua.org.migdal.session.LocationInfo;
import ua.org.migdal.session.RequestContext;

@Controller
public class CrossEntryController {

    @Inject
    private PlatformTransactionManager txManager;

    @Inject
    private RequestContext requestContext;

    @Inject
    private IndexController indexController;

    @GetMapping("/add-cross")
    public String crossAdd(@RequestParam(required = false) String sourceName,
                           @RequestParam(required = false) long sourceId,
                           @RequestParam int linkType,
                           Model model) {
        crossAddLocationInfo(model);

        model.asMap().computeIfAbsent("crossEntryAddForm", key -> new CrossEntryAddForm(sourceName, sourceId, linkType));
        return "cross-entry-add";
    }

    public LocationInfo crossAddLocationInfo(Model model) {
        return new LocationInfo(model)
                .withUri("/add-cross")
                .withParent(indexController.indexLocationInfo(null))
                .withPageTitle("Добавление перекрестной ссылки");
    }

    @PostMapping("/actions/cross-entry/add")
    public String actionCrossAdd(
            @ModelAttribute @Valid CrossEntryAddForm crossEntryAddForm,
            Errors errors,
            RedirectAttributes redirectAttributes) {
        new ControllerAction(CrossEntryController.class, "actionCrossAdd", errors)
                .transactional(txManager)
                .execute(() -> {

                    return null;
                });

        if (!errors.hasErrors()) {
            return "redirect:" + requestContext.getOrigin();
        } else {
            redirectAttributes.addFlashAttribute("errors", errors);
            redirectAttributes.addFlashAttribute("crossEntryAddForm", crossEntryAddForm);
            return "redirect:" + requestContext.getBack();
        }
    }

}
