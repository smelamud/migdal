package ua.org.migdal.session;

import java.util.HashSet;
import java.util.Set;
import java.util.regex.Pattern;

import javax.annotation.PostConstruct;
import javax.servlet.http.HttpServletRequest;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.context.annotation.ScopedProxyMode;
import org.springframework.stereotype.Component;
import org.springframework.web.context.annotation.RequestScope;

import ua.org.migdal.Config;
import ua.org.migdal.data.User;
import ua.org.migdal.data.UserRight;
import ua.org.migdal.manager.UserManager;
import ua.org.migdal.util.Utils;

@RequestScope(proxyMode = ScopedProxyMode.INTERFACES)
@Component
public class RequestContextImpl implements RequestContext {

    private static final Pattern LOCATION_REGEX = Pattern.compile("^(/[a-zA-z0-9-~@]*)+(\\?.*)?$");

    private static ThreadLocal<RequestContext> instance = new ThreadLocal<>();

    @Autowired
    private Config config;

    @Autowired
    private Session session;

    @Autowired
    private HttpServletRequest request;

    @Autowired
    private SubdomainUtils subdomainUtils;

    @Autowired
    private UserManager userManager;

    private boolean sessionProcessed;
    private boolean requestProcessed;

    private String location;
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

    @PostConstruct
    public void init() {
        instance.set(this);
    }

    public static RequestContext getInstance() {
        return instance.get();
    }

    private void touchSession() {
        long now = System.currentTimeMillis();
        if (session.getUserId() > 0 && session.getLast() + session.getDuration() * 3600 * 1000 < now) {
            session.setUserId(0);
            session.setRealUserId(userManager.getGuestId());
        }
        if (session.getUserId() <= 0 && session.getRealUserId() <= 0) { // freshly created session
            session.setRealUserId(userManager.getGuestId());
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
            user = userManager.get(session.getUserId());
            if (user == null) {
                session.setUserId(0);
                session.setRealUserId(userManager.getGuestId());
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
        userGroups.addAll(userManager.getGroupIdsByUserId(userId));
        userManager.updateLastOnline(realUserId, Utils.now());
    }

    private void processRequest() {
        if (requestProcessed) {
            return;
        }
        requestProcessed = true;

        location = SubdomainUtils.createLocalBuilderFromRequest(request).toUriString();
        String hostname = SubdomainUtils.createBuilderFromRequest(request).build().getHost();
        subdomain = subdomainUtils.validateSubdomain(hostname).getSubdomain();
        printMode = "1".equals(request.getParameter("print"));
        back = request.getParameter("back");
        back = back != null && LOCATION_REGEX.matcher(back).matches() ? back : null;
    }

    @Override
    public String getLocation() {
        processRequest();
        return location;
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