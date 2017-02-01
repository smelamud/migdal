package daily.coin;

import java.util.HashMap;
import java.util.Map;

import javax.servlet.http.HttpServletRequest;

import org.springframework.http.HttpStatus;
import org.springframework.stereotype.Component;
import org.springframework.web.servlet.ModelAndView;

@Component
public class ErrorViewResolver implements org.springframework.boot.autoconfigure.web.ErrorViewResolver {

    @Override
    public ModelAndView resolveErrorView(
            HttpServletRequest httpServletRequest,
            HttpStatus httpStatus,
            Map<String, Object> map) {
        Map<String, Object> model = new HashMap<>(map);
        model.put("errorMessage", model.getOrDefault("message", ""));
        return new ModelAndView("fail", model);
    }

}
