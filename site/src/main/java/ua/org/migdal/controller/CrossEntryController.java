package ua.org.migdal.controller;

import javax.inject.Inject;
import javax.validation.Valid;

import org.springframework.stereotype.Controller;
import org.springframework.transaction.PlatformTransactionManager;
import org.springframework.ui.Model;
import org.springframework.util.StringUtils;
import org.springframework.validation.Errors;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.ModelAttribute;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.servlet.mvc.support.RedirectAttributes;

import ua.org.migdal.data.CrossEntry;
import ua.org.migdal.data.Entry;
import ua.org.migdal.data.LinkType;
import ua.org.migdal.form.CrossEntryAddForm;
import ua.org.migdal.form.CrossEntryDeleteForm;
import ua.org.migdal.manager.CrossEntryManager;
import ua.org.migdal.manager.EntryManager;
import ua.org.migdal.manager.IdentManager;
import ua.org.migdal.session.LocationInfo;
import ua.org.migdal.session.RequestContext;

@Controller
public class CrossEntryController {

    @Inject
    private PlatformTransactionManager txManager;

    @Inject
    private RequestContext requestContext;

    @Inject
    private IdentManager identManager;

    @Inject
    private EntryManager entryManager;

    @Inject
    private CrossEntryManager crossEntryManager;

    @Inject
    private IndexController indexController;

    @GetMapping("/add-cross")
    public String crossAdd(@RequestParam(required = false) String sourceName,
                           @RequestParam(required = false) Long sourceId,
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
                    if (!requestContext.isUserModerator()) {
                        return "notModerator";
                    }
                    if (StringUtils.isEmpty(crossEntryAddForm.getSourceName())
                            && crossEntryAddForm.getSourceId() <= 0) {
                        return "noSource";
                    }
                    if (LinkType.valueOf(crossEntryAddForm.getLinkType()) == null) {
                        return "linkType.invalidLinkType";
                    }
                    Entry source = null;
                    if (crossEntryAddForm.getSourceId() > 0) {
                        source = entryManager.get(crossEntryAddForm.getSourceId());
                        if (source == null) {
                            return "sourceId.noSourceEntry";
                        }
                    }
                    long peerId = identManager.idOrIdent(crossEntryAddForm.getPeerIdent());
                    Entry peer = peerId > 0 ? entryManager.get(peerId) : null;
                    if (peer == null) {
                        return "peerIdent.noPeerEntry";
                    }

                    CrossEntry crossEntry = new CrossEntry();
                    crossEntryAddForm.toCrossEntry(crossEntry, source, peer);
                    crossEntryManager.save(crossEntry);

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

    @GetMapping("/actions/cross-entry/delete")  // FIXME leave only POST
    @PostMapping("/actions/cross-entry/delete")
    public String actionCrossDelete(
            @ModelAttribute @Valid CrossEntryDeleteForm crossEntryDeleteForm,
            Errors errors,
            RedirectAttributes redirectAttributes) {
        new ControllerAction(EntryController.class, "actionCrossDelete", errors)
                .transactional(txManager)
                .execute(() -> {
                    if (!requestContext.isUserModerator()) {
                        return "notModerator";
                    }
                    CrossEntry crossEntry = crossEntryManager.get(crossEntryDeleteForm.getId());
                    if (crossEntry == null) {
                        return "noCross";
                    }
                    crossEntryManager.delete(crossEntry);
                    return null;
                });

        if (!errors.hasErrors()) {
            return "redirect:" + requestContext.getBack();
        } else {
            redirectAttributes.addFlashAttribute("errors", errors);
            return "redirect:" + requestContext.getBack();
        }
    }

}
