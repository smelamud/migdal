package ua.org.migdal.session;

import java.util.Arrays;

import javax.inject.Inject;

import org.springframework.stereotype.Component;

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
        int pos = hostname.indexOf('.');
        if (pos < 0) {
            return new SubdomainInfo(null, config.getSiteDomain());
        }
        String defaultSubdomain = config.getSubdomains()[0];
        String subdomain = hostname.substring(0, pos);
        String domain = hostname.substring(pos + 1);
        if (domain.equals(config.getSiteDomain())) {
            if (isValidSubdomain(subdomain)) {
                return new SubdomainInfo(subdomain, null);
            } else {
                return new SubdomainInfo(subdomain, String.join(".", defaultSubdomain, domain));
            }
        } else {
            if (isValidSubdomain(subdomain)) {
                return new SubdomainInfo(subdomain, String.join(".", subdomain, config.getSiteDomain()));
            } else {
                return new SubdomainInfo(subdomain, String.join(".", defaultSubdomain, config.getSiteDomain()));
            }
        }
    }

    private boolean isValidSubdomain(String subdomain) {
        return Arrays.asList(config.getSubdomains()).contains(subdomain);
    }

}
