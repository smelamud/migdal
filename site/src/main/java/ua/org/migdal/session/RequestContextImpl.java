package ua.org.migdal.session;

import java.util.HashSet;
import java.util.Set;
import java.util.regex.Pattern;
import java.util.stream.Collectors;

import javax.servlet.http.HttpServletRequest;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.context.annotation.ScopedProxyMode;
import org.springframework.stereotype.Component;
import org.springframework.web.context.annotation.RequestScope;

import ua.org.migdal.Config;
import ua.org.migdal.data.User;
import ua.org.migdal.data.UserRight;
import ua.org.migdal.manager.UsersManager;
import ua.org.migdal.util.Utils;

@RequestScope(proxyMode = ScopedProxyMode.INTERFACES)
@Component
public class RequestContextImpl implements RequestContext {

    private final static Pattern LOCATION_REGEX = Pattern.compile("^(/[a-zA-z0-9-~@]*)+(\\?.*)?$");

    @Autowired
    private Config config;

    @Autowired
    private Session session;

    @Autowired
    private HttpServletRequest request;

    @Autowired
    private SubdomainUtils subdomainUtils;

    @Autowired
    private UsersManager usersManager;

    private boolean sessionProcessed;
    private boolean requestProcessed;

    private String subdomain;
    private String back;
    private Boolean printMode;
    private long userId;
    private long realUserId;
    private Set<Long> userGroups = new HashSet<>();
    private String userLogin;
    private String userFolder;
    private short userHidden;
    private long userRights;

    private void touchSession() {
        long now = System.currentTimeMillis();
        if (session.getUserId() > 0 && session.getLast() + session.getDuration() * 3600 * 1000 < now) {
            session.setUserId(0);
            session.setRealUserId(usersManager.getGuestId());
        }
        if (session.getUserId() <= 0 && session.getRealUserId() <= 0) { // freshly created session
            session.setRealUserId(usersManager.getGuestId());
        }
        session.setLast(now);
    }

    private void processSession() {
        if (sessionProcessed) {
            return;
        }
        sessionProcessed = true;

        touchSession();

        User user = null;
        if (session.getUserId() > 0) {
            user = usersManager.get(session.getUserId());
            if (user == null) {
                session.setUserId(0);
                session.setRealUserId(usersManager.getGuestId());
            }
        }

        userId = session.getUserId();
        realUserId = session.getRealUserId();

        if (user == null) {
            if (realUserId > 0) {
                userLogin = config.getGuestLogin();
            }
            return;
        }

        userLogin = user.getLogin();
        userFolder = user.getFolder();
        userHidden = user.getHidden();
        userRights = user.getRights();
        if (isUserAdminUsers() && userHidden > 0) {
            userHidden--;
        }
        userGroups.addAll(usersManager.getGroupIdsByUserId(userId));
        usersManager.updateLastOnline(realUserId, Utils.now());
    }

    private void processRequest() {
        if (requestProcessed) {
            return;
        }
        requestProcessed = true;

        String hostname = SubdomainUtils.createBuilderFromRequest(request).build().getHost();
        subdomain = subdomainUtils.validateSubdomain(hostname).getSubdomain();
        printMode = "1".equals(request.getParameter("print"));
        back = request.getParameter("back");
        back = back != null && LOCATION_REGEX.matcher(back).matches() ? back : null;
    }

    @Override
    public String getSubdomain() {
        processRequest();
        return subdomain;
    }

    @Override
    public boolean isWww() {
        return getSubdomain().equals("www");
    }

    @Override
    public boolean isEnglish() {
        return getSubdomain().equals("english");
    }

    @Override
    public String getBack() {
        processRequest();
        return back != null ? back : "/";
    }

    public boolean isHasBack() {
        processRequest();
        return back != null;
    }

    @Override
    public boolean isPrintMode() {
        processRequest();
        return printMode;
    }

    @Override
    public long getUserId() {
        processSession();
        return userId;
    }

    @Override
    public boolean isLogged() {
        return getUserId() > 0;
    }

    @Override
    public long getRealUserId() {
        processSession();
        return realUserId;
    }

    @Override
    public Set<Long> getUserGroups() {
        processSession();
        return userGroups;
    }

    @Override
    public String getUserLogin() {
        processSession();
        return userLogin;
    }

    @Override
    public String getUserFolder() {
        processSession();
        return userFolder;
    }

    @Override
    public short getUserHidden() {
        processSession();
        return userHidden;
    }

    @Override
    public boolean isMigdalStudent() {
        processSession();
        return (userRights & UserRight.MIGDAL_STUDENT.getValue()) != 0;
    }

    @Override
    public boolean isUserAdminUsers() {
        processSession();
        return (userRights & UserRight.ADMIN_USERS.getValue()) != 0;
    }

    @Override
    public boolean isUserAdminTopics() {
        processSession();
        return (userRights & UserRight.ADMIN_TOPICS.getValue()) != 0;
    }

    @Override
    public boolean isUserModerator() {
        processSession();
        return (userRights & UserRight.MODERATOR.getValue()) != 0;
    }

    @Override
    public boolean isUserAdminDomain() {
        processSession();
        return (userRights & UserRight.ADMIN_DOMAIN.getValue()) != 0;
    }

}