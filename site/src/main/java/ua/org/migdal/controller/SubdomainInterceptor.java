package ua.org.migdal.controller;

import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import javax.inject.Inject;
import org.springframework.stereotype.Component;
import org.springframework.web.servlet.handler.HandlerInterceptorAdapter;
import org.springframework.web.util.UriComponentsBuilder;

import ua.org.migdal.session.SubdomainUtils;

@Component
public class SubdomainInterceptor extends HandlerInterceptorAdapter {

    @Inject
    private SubdomainUtils subdomainUtils;

    @Override
    public boolean preHandle(HttpServletRequest request, HttpServletResponse response, Object handler) throws Exception {
        UriComponentsBuilder builder = SubdomainUtils.createBuilderFromRequest(request);
        SubdomainUtils.SubdomainInfo subdomainInfo = subdomainUtils.validateSubdomain(builder.build().getHost());
        if (subdomainInfo.getRedirectTo() == null) {
            return true;
        }

        builder.host(subdomainInfo.getRedirectTo());
        response.sendRedirect(response.encodeRedirectURL(builder.toUriString()));
        return false;
    }

}
