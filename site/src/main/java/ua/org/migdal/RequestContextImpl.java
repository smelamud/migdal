package ua.org.migdal;

import java.util.List;

import javax.annotation.PostConstruct;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.context.annotation.ScopedProxyMode;
import org.springframework.stereotype.Component;
import org.springframework.web.context.annotation.RequestScope;

@RequestScope(proxyMode = ScopedProxyMode.INTERFACES)
@Component
public class RequestContextImpl implements RequestContext {

    @Autowired
    private Session session;

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

    @Override
    public long getUserId() {
        return userId;
    }

    @Override
    public void setUserId(long userId) {
        this.userId = userId;
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