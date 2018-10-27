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

import ua.org.migdal.data.Posting;

@Service
public class TopicsService {

    private static TopicsService instance;

    private Map<String, Pair<Object, Method>> mappings = new HashMap<>();

    @Inject
    private ApplicationContext applicationContext;

    public static TopicsService getInstance() {
        return instance;
    }

    @PostConstruct
    public void init() {
        instance = this;

        for (Object controller : applicationContext.getBeansWithAnnotation(Controller.class).values()) {
            for (Method method : controller.getClass().getDeclaredMethods()) {
                TopicsMapping mapping = method.getAnnotation(TopicsMapping.class);
                if (mapping == null) {
                    continue;
                }
                if (method.getParameterCount() != 1 && method.getParameterCount() != 2) {
                    continue;
                }
                if (method.getParameterTypes()[method.getParameterCount() - 1] != Model.class) {
                    continue;
                }
                if (method.getParameterCount() == 2 && method.getParameterTypes()[0] != Posting.class) {
                    continue;
                }
                if (!StringUtils.isEmpty(mapping.value())) {
                    mappings.put(mapping.value(), Pair.of(controller, method));
                }
            }
        }
    }

    public void executeMethod(String templateName, Posting posting, Model model) {
        Pair<Object, Method> target = mappings.get(templateName);
        if (target == null) {
            return;
        }
        Object controller = target.getFirst();
        Method method = target.getSecond();
        try {
            if (method.getParameterCount() == 1) {
                method.invoke(controller, model);
            }
            if (method.getParameterCount() == 2) {
                method.invoke(controller, posting, model);
            }
        } catch (IllegalAccessException | InvocationTargetException e) {
            e.printStackTrace();
        }
    }

}
