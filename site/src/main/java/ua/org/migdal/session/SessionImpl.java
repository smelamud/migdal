package ua.org.migdal.session;

import java.io.Serializable;

import org.springframework.context.annotation.ScopedProxyMode;
import org.springframework.stereotype.Component;
import org.springframework.web.context.annotation.SessionScope;

@SessionScope(proxyMode = ScopedProxyMode.INTERFACES)
@Component
public class SessionImpl implements Session, Serializable {

    private static final long serialVersionUID = -5758885365302557269L;

    private long userId;
    private long realUserId;
    private long last;
    private int duration;

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
    public long getLast() {
        return last;
    }

    @Override
    public void setLast(long last) {
        this.last = last;
    }

    @Override
    public int getDuration() {
        return duration;
    }

    @Override
    public void setDuration(int duration) {
        this.duration = duration;
    }

}
