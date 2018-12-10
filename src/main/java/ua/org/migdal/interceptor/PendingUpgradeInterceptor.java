package ua.org.migdal.interceptor;

import javax.inject.Inject;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.springframework.stereotype.Component;
import org.springframework.web.servlet.handler.HandlerInterceptorAdapter;

import ua.org.migdal.Config;
import ua.org.migdal.controller.exception.PendingUpgradeException;
import ua.org.migdal.util.UriUtils;

@Component
public class PendingUpgradeInterceptor extends HandlerInterceptorAdapter {

    @Inject
    private Config config;

    @Override
    public boolean preHandle(HttpServletRequest request, HttpServletResponse response, Object handler) throws Exception {
        if (config.isPendingUpgrade()) {
            String path = UriUtils.createLocalBuilderFromRequest(request).build().getPath();
            if (path != null && (path.startsWith("/pics/") || path.equals("/error"))) {
                return true;
            }
            throw new PendingUpgradeException();
        }

        return true;
    }

}
