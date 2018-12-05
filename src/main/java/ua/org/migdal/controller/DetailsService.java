package ua.org.migdal.controller;

import java.lang.reflect.InvocationTargetException;
import java.lang.reflect.Method;
import java.util.HashMap;
import java.util.Map;

import javax.annotation.PostConstruct;
import javax.inject.Inject;

import org.springframework.context.ApplicationContext;
import org.springframework.data.util.Pair;
import org.springframework.stereotype.Controller;
import org.springframework.stereotype.Service;
import org.springframework.ui.Model;
import org.springframework.util.StringUtils;

import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.data.Posting;

@Service
public class DetailsService {

    private static DetailsService instance;

    private Map<String, Pair<Object, Method>> mappings = new HashMap<>();

    @Inject
    private ApplicationContext applicationContext;

    public static DetailsService getInstance() {
        return instance;
    }

    @PostConstruct
    public void init() {
        instance = this;

        for (Object controller : applicationContext.getBeansWithAnnotation(Controller.class).values()) {
            for (Method method : controller.getClass().getDeclaredMethods()) {
                DetailsMapping mapping = method.getAnnotation(DetailsMapping.class);
                if (mapping == null) {
                    continue;
                }
                if (method.getParameterCount() != 2) {
                    continue;
                }
                if (method.getParameterTypes()[0] != Posting.class || method.getParameterTypes()[1] != Model.class) {
                    continue;
                }
                if (!StringUtils.isEmpty(mapping.value())) {
                    mappings.put(mapping.value(), Pair.of(controller, method));
                }
            }
        }
    }

    public String callMapping(String templateName, Posting posting, Model model) throws PageNotFoundException {
        Pair<Object, Method> target = mappings.get(templateName);
        if (target == null) {
            return null;
        }
        Object controller = target.getFirst();
        Method method = target.getSecond();
        Object returnValue = null;
        try {
            if (method.getParameterCount() == 2) {
                returnValue = method.invoke(controller, posting, model);
            }
        } catch (IllegalAccessException e) {
            e.printStackTrace();
        } catch (InvocationTargetException e) {
            if (e.getTargetException() instanceof PageNotFoundException) {
                throw (PageNotFoundException) e.getTargetException();
            }
            e.printStackTrace();
        }
        return returnValue != null ? returnValue.toString() : null;
    }

}
