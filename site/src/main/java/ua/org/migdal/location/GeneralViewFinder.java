package ua.org.migdal.location;

import java.lang.reflect.Method;
import java.util.SortedSet;
import java.util.TreeSet;

import javax.annotation.PostConstruct;
import javax.inject.Inject;

import org.springframework.context.ApplicationContext;
import org.springframework.stereotype.Controller;
import org.springframework.stereotype.Service;
import ua.org.migdal.controller.IndexController;
import ua.org.migdal.data.Posting;

@Service
public class GeneralViewFinder {

    @Inject
    private ApplicationContext applicationContext;

    @Inject
    private IndexController indexController;

    private SortedSet<GeneralView> generalViews = new TreeSet<>();

    @PostConstruct
    public void init() {
        for (Object controller : applicationContext.getBeansWithAnnotation(Controller.class).values()) {
            for (Method method : controller.getClass().getDeclaredMethods()) {
                if (!method.isAnnotationPresent(GeneralViewFor.class)) {
                    continue;
                }

                GeneralView.validate(controller.getClass(), method);

                GeneralViewFor annotation = method.getAnnotation(GeneralViewFor.class);
                generalViews.add(new GeneralView(annotation.value(), annotation.priority(), controller, method));
            }
        }
    }

    public LocationInfo findFor(Posting posting) {
        for (GeneralView generalView : generalViews) {
            if (!generalView.matches(posting.getCatalog())) {
                continue;
            }
            LocationInfo locationInfo = generalView.call(posting);
            if (locationInfo != null) {
                return locationInfo;
            }
        }
        return indexController.indexLocationInfo(null);
    }

}
