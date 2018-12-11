package ua.org.migdal.interceptor;

import java.io.IOException;

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
        if (redirect("discuss/", "comments", path, builder, response)
            || redirect("discuss/reply/", "comment-add", path, builder, response)) {
            return false;
        }

        return true;
    }

    private boolean redirect(String suffix, String replacement, String path,
                             UriComponentsBuilder builder, HttpServletResponse response) throws IOException {
        if (path.endsWith(suffix)) {
            String newPath = "/" + path.substring(0, path.length() - suffix.length());
            response.sendRedirect(builder.replacePath(newPath).fragment(replacement).build(true).toUriString());
            return true;
        }
        return false;
    }

}
