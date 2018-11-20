package ua.org.migdal.controller;

import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.springframework.stereotype.Component;
import org.springframework.web.servlet.handler.HandlerInterceptorAdapter;
import org.springframework.web.util.UriComponentsBuilder;
import ua.org.migdal.controller.exception.PageNotFoundException;
import ua.org.migdal.session.SubdomainUtils;

@Component
public class TrapInterceptor extends HandlerInterceptorAdapter {

    @Override
    public boolean preHandle(HttpServletRequest request, HttpServletResponse response, Object handler) throws Exception {
        UriComponentsBuilder builder = SubdomainUtils.createLocalBuilderFromRequest(request);
        if (!TrapHandler.fallsUnder(builder)) {
            return true;
        }
        String redirectTo = TrapHandler.trap(builder);
        if (redirectTo == null) {
            throw new PageNotFoundException();
        }
        response.sendRedirect(redirectTo);
        return false;
    }

}
