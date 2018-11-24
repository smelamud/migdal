package ua.org.migdal.controller;

import java.util.regex.Pattern;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import javax.inject.Inject;
import org.springframework.stereotype.Component;
import org.springframework.web.servlet.handler.HandlerInterceptorAdapter;
import org.springframework.web.util.UriComponents;
import org.springframework.web.util.UriComponentsBuilder;

import ua.org.migdal.Config;
import ua.org.migdal.session.SubdomainUtils;
import ua.org.migdal.session.SubdomainUtils.SubdomainInfo;
import ua.org.migdal.util.UriUtils;

@Component
public class SubdomainInterceptor extends HandlerInterceptorAdapter {

    private static final Object[] ENGLISH_PATHS = new Object[] {
            "/",
            "/migdal/",
            Pattern.compile("^/migdal/\\d+/$"),
            "/tzdaka/",
            "/jewish-odessa-tours/",
            "/migdal-or/",
            "/museum/",
            "/virtual-museum/",
            "/mazltov/",
            "/add-review/",
            "/reorder-reviews/",
            "/events/",
            Pattern.compile("^/events/\\d+/$"),
            Pattern.compile("^/events/add-[a-z]+/$"),
            Pattern.compile("^/css/.*$"),
            Pattern.compile("^/js/.*$"),
            Pattern.compile("^/favicon.ico/.*$"),
            Pattern.compile("^/pics/.*$"),
            Pattern.compile("^/actions/.*$")
    };

    @Inject
    private Config config;

    @Inject
    private SubdomainUtils subdomainUtils;

    @Override
    public boolean preHandle(HttpServletRequest request, HttpServletResponse response, Object handler) throws Exception {
        UriComponentsBuilder builder = UriUtils.createBuilderFromRequest(request);
        UriComponents uriComponents = builder.build();
        SubdomainInfo subdomainInfo =
                validatePath(subdomainUtils.validateSubdomain(uriComponents.getHost()), uriComponents.getPath());
        if (subdomainInfo.getRedirectTo() == null) {
            return true;
        }

        builder.host(subdomainInfo.getRedirectTo());
        response.sendRedirect(response.encodeRedirectURL(builder.build(true).toUriString()));
        return false;
    }

    private SubdomainInfo validatePath(SubdomainInfo subdomainInfo, String path) {
        if (subdomainInfo.getRedirectTo() != null || !subdomainInfo.getSubdomain().equals("english") || path == null) {
            return subdomainInfo;
        }
        path = path.isEmpty() || path.charAt(path.length() - 1) != '/' ? path + '/' : path;
        for (Object check : ENGLISH_PATHS) {
            if (check instanceof String && path.equals(check)) {
                return subdomainInfo;
            }
            if (check instanceof Pattern && ((Pattern) check).matcher(path).matches()) {
                return subdomainInfo;
            }
        }
        return new SubdomainInfo(subdomainInfo.getSubdomain(),
                                 String.join(".", config.getSubdomains()[0], config.getSiteDomain()));
    }

}
