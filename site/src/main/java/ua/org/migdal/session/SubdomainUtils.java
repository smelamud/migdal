package ua.org.migdal.session;

import java.util.Arrays;

import javax.servlet.http.HttpServletRequest;

import javax.inject.Inject;
import org.springframework.stereotype.Component;
import org.springframework.web.util.UriComponentsBuilder;
import ua.org.migdal.Config;

@Component
public class SubdomainUtils {

    @Inject
    private Config config;

    public static class SubdomainInfo {

        private String subdomain;
        private String redirectTo;

        public SubdomainInfo(String subdomain, String redirectTo) {
            this.subdomain = subdomain;
            this.redirectTo = redirectTo;
        }

        public String getSubdomain() {
            return subdomain;
        }

        public String getRedirectTo() {
            return redirectTo;
        }

    }

    public SubdomainInfo validateSubdomain(String hostname) {
        if (hostname.equals(config.getSiteDomain())) {
            return new SubdomainInfo(config.getSubdomains()[0], null);
        }
        int pos = hostname.indexOf('.');
        if (pos < 0) {
            return new SubdomainInfo(null, config.getSiteDomain());
        }
        String subdomain = hostname.substring(0, pos);
        String domain = hostname.substring(pos + 1);
        if (domain.equals(config.getSiteDomain())) {
            if (Arrays.asList(config.getSubdomains()).contains(subdomain)) {
                return new SubdomainInfo(subdomain, null);
            } else {
                return new SubdomainInfo(subdomain, String.join(".", config.getSubdomains()[0], domain));
            }
        } else {
            return new SubdomainInfo(subdomain, String.join(".", subdomain, config.getSiteDomain()));
        }
    }

    public static UriComponentsBuilder createBuilderFromRequest(HttpServletRequest request) {
        return UriComponentsBuilder
                .fromHttpUrl(request.getRequestURL().toString())
                .query(request.getQueryString());
    }

    public static UriComponentsBuilder createLocalBuilderFromRequest(HttpServletRequest request) {
        return UriComponentsBuilder
                .fromPath(request.getRequestURI())
                .query(request.getQueryString());
    }

}
