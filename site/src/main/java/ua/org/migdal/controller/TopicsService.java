package ua.org.migdal.controller;

import java.lang.reflect.InvocationTargetException;
import java.lang.reflect.Method;
import java.util.Arrays;
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
                if (!StringUtils.isEmpty(mapping.value())) {
                    mappings.put(mapping.value(), Pair.of(controller, method));
                }
            }
        }
    }

    public void executeMethod(String templateName, Object[] parameters, Model model) {
        Pair<Object, Method> target = mappings.get(templateName);
        if (target == null) {
            return;
        }
        Method method = target.getSecond();
        parameters = parameters != null ? parameters : new Object[0];
        if (method.getParameterCount() != parameters.length + 1) {
            return;
        }
        if (method.getParameterTypes()[parameters.length] != Model.class) {
            return;
        }
        try {
            Object[] invocationParameters = Arrays.copyOf(parameters, parameters.length + 1);
            invocationParameters[parameters.length] = model;
            method.invoke(target.getFirst(), invocationParameters);
        } catch (IllegalAccessException | InvocationTargetException e) {
            e.printStackTrace();
        }
    }

}
