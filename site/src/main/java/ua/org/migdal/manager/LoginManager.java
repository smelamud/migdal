package ua.org.migdal.manager;

import java.security.NoSuchAlgorithmException;
import javax.inject.Inject;

import org.springframework.stereotype.Service;
import ua.org.migdal.Config;
import ua.org.migdal.data.User;
import ua.org.migdal.session.Session;
import ua.org.migdal.util.Password;

@Service
public class LoginManager {

    @Inject
    private Config config;

    @Inject
    private Session session;

    @Inject
    private UserManager userManager;

    public String login(String login, String password, boolean myComputer) throws NoSuchAlgorithmException {
        User user = userManager.getByLogin(login);
        if (!Password.validate(user, password)) {
            return "incorrect";
        }
        if (user.isNoLogin()) {
            return "banned";
        }
        session.setUserId(user.getId());
        session.setRealUserId(user.getId());
        session.setDuration(myComputer
                ? config.getSessionTimeoutLong()
                : config.getSessionTimeoutShort());
        return null;
    }

    public void logout() {
        if (session.getUserId() > 0 && session.getUserId() != session.getRealUserId()) {
            session.setUserId(session.getRealUserId());
        } else {
            session.setUserId(0);
            session.setRealUserId(userManager.getGuestId());
        }
    }

}