package ua.org.migdal.controller;

import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.springframework.stereotype.Component;
import org.springframework.web.servlet.handler.HandlerInterceptorAdapter;
import org.springframework.web.util.UriComponentsBuilder;
import ua.org.migdal.session.SubdomainUtils;

@Component
public class NoPrintInterceptor extends HandlerInterceptorAdapter {

    @Override
    public boolean preHandle(HttpServletRequest request, HttpServletResponse response, Object handler) throws Exception {
        UriComponentsBuilder builder = SubdomainUtils.createLocalBuilderFromRequest(request);
        if (builder.build().getQueryParams().containsKey("print")) {
            response.sendRedirect(builder.replaceQueryParam("print").build(true).toUriString());
            return false;
        }
        return true;
    }

}
