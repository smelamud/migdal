package ua.org.migdal.mail;

import java.util.Collections;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;
import ua.org.migdal.data.User;
import ua.org.migdal.data.UserRight;
import ua.org.migdal.mail.exception.MailServiceException;

@Service
public class MailController {

    @Autowired
    private MailService mailService;

    public void register(User user) throws MailServiceException {
        mailService.sendMail(user, true, "register", Collections.singletonMap("user", user));
    }

    public void registering(User user) throws MailServiceException {
        mailService.sendMailToAdmins(UserRight.ADMIN_USERS, false, "registering",
                Collections.singletonMap("user", user));
    }

}
