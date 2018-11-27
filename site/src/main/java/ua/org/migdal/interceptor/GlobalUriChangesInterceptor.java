package ua.org.migdal.interceptor;

import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.springframework.stereotype.Component;
import org.springframework.web.servlet.handler.HandlerInterceptorAdapter;
import org.springframework.web.util.UriComponents;
import org.springframework.web.util.UriComponentsBuilder;
import ua.org.migdal.util.CatalogUtils;
import ua.org.migdal.util.UriUtils;

@Component
public class GlobalUriChangesInterceptor extends HandlerInterceptorAdapter {

    @Override
    public boolean preHandle(HttpServletRequest request, HttpServletResponse response, Object handler) throws Exception {
        UriComponentsBuilder builder = UriUtils.createLocalBuilderFromRequest(request);
        UriComponents components = builder.build();

        if (components.getQueryParams().containsKey("print")) {
            response.sendRedirect(builder.replaceQueryParam("print").build(true).toUriString());
            return false;
        }

        String path = CatalogUtils.normalize(components.getPath());
        if (path.endsWith("discuss/")) {
            String newPath = "/" + path.substring(0, path.length() - 8);
            response.sendRedirect(builder.replacePath(newPath).fragment("comments").build(true).toUriString());
            return false;
        }

        return true;
    }

}
