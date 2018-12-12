package ua.org.migdal.controller;

import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.GetMapping;
import ua.org.migdal.controller.exception.InternalServerErrorException;
import ua.org.migdal.controller.exception.PageNotFoundException;

@Controller
public class ErrorViewController {

    @GetMapping("/fail/404")
    public void pageNotFound() throws PageNotFoundException {
        throw new PageNotFoundException();
    }

    @GetMapping("/fail/500")
    public void internalServerError() throws InternalServerErrorException {
        throw new InternalServerErrorException();
    }

}
