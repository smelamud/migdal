package ua.org.migdal.session;

import java.util.List;
import java.util.regex.Pattern;

import javax.annotation.PostConstruct;
import javax.servlet.http.HttpServletRequest;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.context.annotation.ScopedProxyMode;
import org.springframework.stereotype.Component;
import org.springframework.web.context.annotation.RequestScope;

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
    public void setUserId(long userId) {
        this.userId = userId;
    }

    @Override
    public boolean isLogged() {
        return userId > 0;
    }

    @Override
    public long getRealUserId() {
        return realUserId;
    }

    @Override
    public void setRealUserId(long realUserId) {
        this.realUserId = realUserId;
    }

    @Override
    public List<Long> getUserGroups() {
        return userGroups;
    }

    @Override
    public void setUserGroups(List<Long> userGroups) {
        this.userGroups = userGroups;
    }

    @Override
    public String getUserLogin() {
        return userLogin;
    }

    @Override
    public void setUserLogin(String userLogin) {
        this.userLogin = userLogin;
    }

    @Override
    public String getUserFolder() {
        return userFolder;
    }

    @Override
    public void setUserFolder(String userFolder) {
        this.userFolder = userFolder;
    }

    @Override
    public short getUserHidden() {
        return userHidden;
    }

    @Override
    public void setUserHidden(short userHidden) {
        this.userHidden = userHidden;
    }

    @Override
    public boolean isUserAdminUsers() {
        return userAdminUsers;
    }

    @Override
    public void setUserAdminUsers(boolean userAdminUsers) {
        this.userAdminUsers = userAdminUsers;
    }

    @Override
    public boolean isUserAdminTopics() {
        return userAdminTopics;
    }

    @Override
    public void setUserAdminTopics(boolean userAdminTopics) {
        this.userAdminTopics = userAdminTopics;
    }

    @Override
    public boolean isUserModerator() {
        return userModerator;
    }

    @Override
    public void setUserModerator(boolean userModerator) {
        this.userModerator = userModerator;
    }

    @Override
    public boolean isUserAdminDomain() {
        return userAdminDomain;
    }

    @Override
    public void setUserAdminDomain(boolean userAdminDomain) {
        this.userAdminDomain = userAdminDomain;
    }

    @PostConstruct
    private void init() {
        userId = session.getUserId();
        realUserId = session.getRealUserId();
    }

}