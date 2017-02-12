package ua.org.migdal.session;

import org.springframework.context.annotation.ScopedProxyMode;
import org.springframework.stereotype.Component;
import org.springframework.web.context.annotation.SessionScope;

@SessionScope(proxyMode = ScopedProxyMode.INTERFACES)
@Component
public class SessionImpl implements Session {

    private long userId;
    private long realUserId;

    @Override
    public long getUserId() {
        return userId;
    }

    @Override
    public void setUserId(long userId) {
        this.userId = userId;
    }

    public long getRealUserId() {
        return realUserId;
    }

    public void setRealUserId(long realUserId) {
        this.realUserId = realUserId;
    }

}
