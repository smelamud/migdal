package ua.org.migdal.controller;

import java.util.Map;

import javax.inject.Inject;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.springframework.boot.autoconfigure.web.ErrorProperties;
import org.springframework.boot.autoconfigure.web.servlet.error.BasicErrorController;
import org.springframework.boot.web.servlet.error.DefaultErrorAttributes;
import org.springframework.http.HttpStatus;
import org.springframework.stereotype.Controller;
import org.springframework.web.servlet.ModelAndView;

import ua.org.migdal.Config;

@Controller
public class ErrorController extends BasicErrorController {

    @Inject
    private Config config;

    public ErrorController() {
        super(new DefaultErrorAttributes(), new ErrorProperties());
    }

    @Override
    protected ModelAndView resolveErrorView(HttpServletRequest request, HttpServletResponse response,
                                            HttpStatus status, Map<String, Object> model) {

        switch (status) {
            case NOT_FOUND:
            case INTERNAL_SERVER_ERROR:
                return new ModelAndView("fail", model);
            case SERVICE_UNAVAILABLE:
                if (config.isPendingUpgrade()) {
                    return new ModelAndView("pending-upgrade", model);
                }
                /* fall through */
            default:
                return super.resolveErrorView(request, response, status, model);
        }
    }

}
