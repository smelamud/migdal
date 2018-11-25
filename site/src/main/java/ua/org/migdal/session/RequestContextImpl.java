package ua.org.migdal.session;

import java.util.ArrayList;
import java.util.Collections;
import java.util.HashSet;
import java.util.List;
import java.util.Set;
import java.util.regex.Pattern;

import javax.annotation.PostConstruct;
import javax.inject.Inject;
import javax.servlet.http.Cookie;
import javax.servlet.http.HttpServletRequest;

import org.springframework.context.annotation.ScopedProxyMode;
import org.springframework.stereotype.Component;
import org.springframework.util.StringUtils;
import org.springframework.web.context.annotation.RequestScope;
import org.springframework.web.util.UriComponents;

import ua.org.migdal.Config;
import ua.org.migdal.data.User;
import ua.org.migdal.data.UserRight;
import ua.org.migdal.manager.UserManager;
import ua.org.migdal.util.CatalogUtils;
import ua.org.migdal.util.UriUtils;
import ua.org.migdal.util.Utils;

@RequestScope(proxyMode = ScopedProxyMode.INTERFACES)
@Component
public class RequestContextImpl implements RequestContext {

    private static final Pattern LOCATION_REGEX = Pattern.compile("^(/[a-zA-z0-9-~@]*)+(\\?.*)?$");

    private static ThreadLocal<RequestContext> instance = new ThreadLocal<>();

    @Inject
    private Config config;

    @Inject
    private Session session;

    @Inject
    private HttpServletRequest request;

    @Inject
    private SubdomainUtils subdomainUtils;

    @Inject
    private UserManager userManager;

    private boolean sessionProcessed;
    private boolean requestProcessed;

    private String location;
    private String catalog;
    private String subdomain;
    private int port;
    private String ip;
    private String back;
    private String origin;
    private long userId;
    private User user;
    private long realUserId;
    private User realUser;
    private Set<Long> userGroups = new HashSet<>();
    private String userLogin;
    private String userGuestLoginHint = "";
    private short userHidden;
    private List<String> ogImages = new ArrayList<>();

    @PostConstruct
    public void init() {
        instance.set(this);
    }

    public static RequestContext getInstance() {
        return instance.get();
    }

    private void processSession() {
        if (sessionProcessed) {
            return;
        }
        sessionProcessed = true;

        touchSession();
        readSession();
        extractPermissions();
    }

    @Override
    public void temporarySession(User user, long realUserId) {
        sessionProcessed = true;

        this.user = user;
        userId = user != null ? user.getId() : 0;
        this.realUserId = realUserId;

        extractPermissions();
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

    private void readSession() {
        user = null;
        if (session.getUserId() > 0) {
            user = userManager.get(session.getUserId());
            if (user == null) {
                session.setUserId(0);
                session.setRealUserId(userManager.getGuestId());
            }
        }

        userId = session.getUserId();
        realUserId = session.getRealUserId();
    }

    private void extractPermissions() {
        realUser = userManager.get(realUserId);

        if (user == null) {
            if (realUserId > 0) {
                userLogin = config.getGuestLogin();
            }
            return;
        }

        userLogin = user.getLogin();
        userHidden = user.getHidden();
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

        location = UriUtils.createLocalBuilderFromRequest(request).build(true).toUriString();
        catalog = CatalogUtils.normalize(request.getRequestURI());
        UriComponents uriComponents = UriUtils.createBuilderFromRequest(request).build();
        subdomain = subdomainUtils.validateSubdomain(uriComponents.getHost()).getSubdomain();
        port = uriComponents.getPort();
        ip = request.getRemoteAddr();
        back = request.getParameter("back");
        back = back != null && LOCATION_REGEX.matcher(back).matches() ? back : null;
        origin = request.getParameter("origin");
        origin = origin != null && LOCATION_REGEX.matcher(origin).matches() ? origin : null;

        if (StringUtils.isEmpty(userGuestLoginHint) && request.getCookies() != null) {
            for (Cookie cookie : request.getCookies()) {
                if (cookie.getName().equals("userGuestLoginHint")) {
                    userGuestLoginHint = cookie.getValue();
                }
            }
        }
    }

    @Override
    public String getLocation() {
        processRequest();
        return location;
    }

    @Override
    public String getCatalog() {
        processRequest();
        return catalog;
    }

    @Override
    public String getCatalog(int start, int length) {
        return CatalogUtils.sub(getCatalog(), start, length);
    }

    @Override
    public String getCatalogElement(int n) {
        return CatalogUtils.element(getCatalog(), n);
    }

    @Override
    public int getCatalogLength() {
        return CatalogUtils.length(getCatalog());
    }

    @Override
    public String getSubdomain() {
        processRequest();
        return subdomain;
    }

    @Override
    public int getPort() {
        processRequest();
        return port;
    }

    @Override
    public String getSiteUrl() {
        return getSiteUrl(getSubdomain());
    }

    @Override
    public String getSiteUrl(String subdomain) {
        switch (getPort()) {
            case 80:
                return String.format("http://%s.%s", subdomain, config.getSiteDomain());
            case 443:
                return String.format("https://%s.%s", subdomain, config.getSiteDomain());
            default:
                return String.format("http://%s.%s:%d", subdomain, config.getSiteDomain(), getPort());
        }
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
    public String getWwwSiteUrl() {
        return getSiteUrl("www");
    }

    @Override
    public String getEnglishSiteUrl() {
        return getSiteUrl("english");
    }

    @Override
    public String getIp() {
        processRequest();
        return ip;
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
    public String getOrigin() {
        processRequest();
        return origin != null ? origin : "/";
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
    public User getUser() {
        processSession();
        return user;
    }

    @Override
    public long getRealUserId() {
        processSession();
        return realUserId;
    }

    @Override
    public User getRealUser() {
        processSession();
        return realUser;
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
    public String getUserGuestLoginHint() {
        processRequest();
        return userGuestLoginHint;
    }

    @Override
    public void setUserGuestLoginHint(String userGuestLoginHint) {
        this.userGuestLoginHint = userGuestLoginHint;
    }

    @Override
    public String getUserFolder() {
        processSession();
        return getUser() != null ? getUser().getFolder() : null;
    }

    @Override
    public short getUserHidden() {
        processSession();
        return userHidden;
    }

    private long getUserRights() {
        processSession();
        return getUser() != null ? getUser().getRights() : 0;
    }

    @Override
    public boolean isMigdalStudent() {
        return (getUserRights() & UserRight.MIGDAL_STUDENT.getValue()) != 0;
    }

    @Override
    public boolean isUserAdminUsers() {
        return (getUserRights() & UserRight.ADMIN_USERS.getValue()) != 0;
    }

    @Override
    public boolean isUserAdminTopics() {
        return (getUserRights() & UserRight.ADMIN_TOPICS.getValue()) != 0;
    }

    @Override
    public boolean isUserModerator() {
        return (getUserRights() & UserRight.MODERATOR.getValue()) != 0;
    }

    @Override
    public boolean isUserAdminDomain() {
        return (getUserRights() & UserRight.ADMIN_DOMAIN.getValue()) != 0;
    }

    @Override
    public List<String> getOgImages() {
        return !ogImages.isEmpty() ? ogImages : Collections.singletonList(getSiteUrl() + "/pics/big-tower.gif");
    }

    @Override
    public void addOgImage(String src) {
        ogImages.add(getSiteUrl() + src);
    }

}
