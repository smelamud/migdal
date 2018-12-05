package ua.org.migdal.mail;

import java.util.Collections;
import java.util.HashMap;
import java.util.Map;

import javax.inject.Inject;
import org.springframework.stereotype.Service;
import ua.org.migdal.data.User;
import ua.org.migdal.data.UserRight;
import ua.org.migdal.mail.exception.MailServiceException;

@Service
public class MailController {

    @Inject
    private MailService mailService;

    public void register(User user) throws MailServiceException {
        mailService.sendMail(user, true, "register", Collections.singletonMap("user", user));
    }

    public void registering(User user) throws MailServiceException {
        mailService.sendMailToAdmins(UserRight.ADMIN_USERS, false, "registering",
                Collections.singletonMap("user", user));
    }

    public void confirmed(User user) throws MailServiceException {
        mailService.sendMailToAdmins(UserRight.ADMIN_USERS, false, "confirmed",
                Collections.singletonMap("user", user));
    }

    public void recallPassword(User user, String password) throws MailServiceException {
        Map<String, Object> model = new HashMap<>();
        model.put("user", user);
        model.put("password", password);
        mailService.sendMail(user, true, "recall-password", model);
    }

    public void recallingPassword(User user) throws MailServiceException {
        mailService.sendMailToAdmins(UserRight.ADMIN_USERS, false, "recalling-password",
                Collections.singletonMap("user", user));
    }

}