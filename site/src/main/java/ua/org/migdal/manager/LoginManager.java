package ua.org.migdal.manager;

import java.security.NoSuchAlgorithmException;

import javax.inject.Inject;

import org.springframework.stereotype.Service;
import org.springframework.util.StringUtils;

import ua.org.migdal.Config;
import ua.org.migdal.data.User;
import ua.org.migdal.session.RequestContext;
import ua.org.migdal.session.Session;
import ua.org.migdal.util.Password;

@Service
public class LoginManager {

    @Inject
    private Config config;

    @Inject
    private Session session;

    @Inject
    private RequestContext requestContext;

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

    public String relogin(ReloginVariant reloginVariant, String guestLogin, String login, String password,
                          boolean remember) {
        if (reloginVariant == null) {
            return "reloginIncorrect";
        }
        switch (reloginVariant) {
            case NONE:
            case SAME:
                if (requestContext.isLogged()) {
                    return null;
                }
                /* fallthru */

            case GUEST:
                if (StringUtils.isEmpty(guestLogin)) {
                    return "guestLoginBlank";
                }
                requestContext.setUserGuestLoginHint(guestLogin);
                requestContext.temporarySession(null, userManager.getGuestId());
                return null;

            case LOGIN:
                if (remember) {
                    try {
                        return login(login, password, false);
                    } catch (NoSuchAlgorithmException e) {
                        return "internal-failure";
                    }
                }

                User user = userManager.getByLogin(login);
                try {
                    if (!Password.validate(user, password)) {
                        return "incorrect";
                    }
                } catch (NoSuchAlgorithmException e) {
                    return "internal-failure";
                }
                if (user.isNoLogin()) {
                    return "banned";
                }
                requestContext.temporarySession(user, user.getId());
                return null;
        }
        return "reloginIncorrect";
    }

}