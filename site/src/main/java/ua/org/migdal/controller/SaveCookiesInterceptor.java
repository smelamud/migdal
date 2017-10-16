package ua.org.migdal.controller;

import javax.inject.Inject;
import javax.servlet.http.Cookie;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.springframework.stereotype.Component;
import org.springframework.web.servlet.ModelAndView;
import org.springframework.web.servlet.handler.HandlerInterceptorAdapter;

import ua.org.migdal.session.RequestContext;

@Component
public class SaveCookiesInterceptor extends HandlerInterceptorAdapter {

    @Inject
    private RequestContext requestContext;

    @Override
    public void postHandle(HttpServletRequest request, HttpServletResponse response, Object handler,
                           ModelAndView modelAndView) throws Exception {
        Cookie cookie = new Cookie("userGuestLoginHint", requestContext.getUserGuestLoginHint());
        cookie.setPath("/");
        response.addCookie(cookie);
    }

}