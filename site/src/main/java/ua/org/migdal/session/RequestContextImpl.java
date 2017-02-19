package ua.org.migdal.session;

import java.util.List;
import java.util.regex.Pattern;

import javax.annotation.PostConstruct;
import javax.servlet.http.HttpServletRequest;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.context.annotation.ScopedProxyMode;
import org.springframework.stereotype.Component;
import org.springframework.web.context.annotation.RequestScope;
import ua.org.migdal.manager.UsersManager;

@RequestScope(proxyMode = ScopedProxyMode.INTERFACES)
@Component
public class RequestContextImpl implements RequestContext {

    private final static Pattern LOCATION_REGEX = Pattern.compile("^(/[a-zA-z0-9-~@]*)+(\\?.*)?$");

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
    private List<Long> userGroups;
    private String userLogin;
    private String userFolder;
    private short userHidden;
    private boolean userAdminUsers;
    private boolean userAdminTopics;
    private boolean userModerator;
    private boolean userAdminDomain;

    private void touchSession() {
        long now = System.currentTimeMillis();
        if (session.getUserId() > 0 && session.getLast() + session.getDuration() * 3600 * 1000 < now) {
            session.setUserId(0);
            session.setRealUserId(0);
        }
        session.setLast(now);
    }

    private void processSession() {
        if (sessionProcessed) {
            return;
        }
        sessionProcessed = true;

        if (session.getUserId() <= 0 && session.getRealUserId() <= 0) {
            session.setRealUserId(usersManager.getGuestId());
        }

        realUserId = session.getRealUserId();
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
        return subdomain.equals("www");
    }

    @Override
    public boolean isEnglish() {
        return subdomain.equals("english");
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
        return userId;
    }

    @Override
    public boolean isLogged() {
        return userId > 0;
    }

    @Override
    public long getRealUserId() {
        processSession();
        return realUserId;
    }

    @Override
    public List<Long> getUserGroups() {
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
    public boolean isUserAdminUsers() {
        processSession();
        return userAdminUsers;
    }

    @Override
    public boolean isUserAdminTopics() {
        processSession();
        return userAdminTopics;
    }

    @Override
    public boolean isUserModerator() {
        processSession();
        return userModerator;
    }

    @Override
    public boolean isUserAdminDomain() {
        processSession();
        return userAdminDomain;
    }

    @PostConstruct
    private void init() {
        touchSession();
        userId = session.getUserId();
        realUserId = session.getRealUserId();
    }

}