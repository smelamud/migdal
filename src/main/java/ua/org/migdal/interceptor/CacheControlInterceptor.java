package ua.org.migdal.interceptor;

import java.time.Instant;
import java.time.ZoneOffset;
import java.time.format.DateTimeFormatter;
import java.time.temporal.ChronoUnit;
import java.util.Arrays;
import java.util.concurrent.TimeUnit;

import javax.inject.Inject;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.springframework.http.CacheControl;
import org.springframework.http.HttpHeaders;
import org.springframework.stereotype.Component;
import org.springframework.web.servlet.handler.HandlerInterceptorAdapter;

import ua.org.migdal.Config;
import ua.org.migdal.util.UriUtils;

@Component
public class CacheControlInterceptor extends HandlerInterceptorAdapter {

    private static final String[] STATIC_URLS = {
            "/css/",
            "/favicon.ico",
            "/fonts/",
            "/js/",
            "/pics/",
            "/robots.txt"
    };

    @Inject
    private Config config;

    @Override
    public boolean preHandle(HttpServletRequest request, HttpServletResponse response, Object handler) throws Exception {
        String path = UriUtils.createLocalBuilderFromRequest(request).build().getPath();
        CacheControl cacheControl;
        Instant expires;
        if (path != null && (isStatic(path) || path.startsWith(config.getImageUrl()))) {
            cacheControl = CacheControl.maxAge(1, TimeUnit.DAYS).staleWhileRevalidate(1, TimeUnit.HOURS);
            expires = Instant.now().plus(1, ChronoUnit.DAYS);
        } else {
            cacheControl = CacheControl.noStore().mustRevalidate();
            expires = Instant.now();
        }
        response.setHeader(HttpHeaders.CACHE_CONTROL, cacheControl.getHeaderValue());
        response.setHeader(HttpHeaders.EXPIRES, httpDateTime(expires));
        return true;
    }

    private boolean isStatic(String path) {
        return Arrays.stream(STATIC_URLS).anyMatch(path::startsWith);
    }

    private String httpDateTime(Instant instant) {
        return instant.atOffset(ZoneOffset.UTC).format(DateTimeFormatter.RFC_1123_DATE_TIME);
    }

}
