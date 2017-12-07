package ua.org.migdal.location;

import java.lang.reflect.Method;
import java.util.regex.Pattern;

import org.springframework.ui.Model;

import ua.org.migdal.data.Posting;
import ua.org.migdal.data.Topic;
import ua.org.migdal.location.exception.GeneralViewInvalidParameterException;
import ua.org.migdal.location.exception.GeneralViewInvalidReturnTypeException;
import ua.org.migdal.location.exception.GeneralViewInvocationException;

public class GeneralView implements Comparable<GeneralView> {

    private String pattern;
    private GeneralViewPriority priority;
    private Object controller;
    private Method method;

    private Pattern patternCompiled;

    public GeneralView(String pattern, GeneralViewPriority priority, Object controller, Method method) {
        this.pattern = pattern;
        this.priority = priority;
        this.controller = controller;
        this.method = method;
        patternCompiled = Pattern.compile(pattern);
    }

    public static void validate(Class<?> controllerClass, Method method) {
        for (Class<?> type : method.getParameterTypes()) {
            if (type != Posting.class
                    && type != Topic.class
                    && type != Model.class) {
                throw new GeneralViewInvalidParameterException(controllerClass, method, type);
            }
        }
        if (method.getReturnType() != LocationInfo.class) {
            throw new GeneralViewInvalidReturnTypeException(controllerClass, method);
        }
    }

    public boolean matches(String catalog) {
        return patternCompiled.matcher(catalog).matches();
    }

    public LocationInfo call(Posting posting) {
        Object[] args = new Object[method.getParameterCount()];
        for (int i = 0; i < args.length; i++) {
            Class<?> type = method.getParameterTypes()[i];
            if (type == Posting.class) {
                args[i] = posting;
            } else if (type == Topic.class) {
                args[i] = posting.getTopic();
            } else if (type == Model.class) {
                args[i] = null;
            }
        }
        try {
            return (LocationInfo) method.invoke(controller, args);
        } catch (Exception e) {
            throw new GeneralViewInvocationException(controller.getClass(), method, e);
        }
    }

    @Override
    public int compareTo(GeneralView generalView) {
        int result = Integer.compare(priority.ordinal(), generalView.priority.ordinal());
        if (result == 0) {
            result = -Integer.compare(pattern.length(), generalView.pattern.length());
        }
        return result;
    }

    @Override
    public boolean equals(Object obj) {
        return obj instanceof GeneralView && ((GeneralView) obj).pattern.equals(pattern);
    }

    @Override
    public int hashCode() {
        return pattern.hashCode();
    }

}
