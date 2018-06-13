package ua.org.migdal.form;

import java.io.Serializable;
import javax.validation.constraints.Size;

import org.springframework.util.StringUtils;
import ua.org.migdal.data.Forum;
import ua.org.migdal.session.RequestContext;

public class ForumForm implements Serializable {

    private static final long serialVersionUID = -163740823595098849L;

    private long id;

    private long parentId;

    @Size(max=4096)
    private String body = "";

    private boolean hidden;

    private boolean disabled;

    private short relogin;

    @Size(max=30)
    private String guestLogin = "";

    @Size(max=30)
    private String login = "";

    @Size(max=40)
    private String password = "";

    private boolean remember;

    public ForumForm() {
    }

    public ForumForm(Forum forum, RequestContext requestContext) {
        if (forum == null) {
            return;
        }

        id = forum.getId();
        parentId = forum.getParentId();
        body = forum.getBody();
        hidden = forum.isHidden();
        disabled = forum.isDisabled();
        guestLogin = !StringUtils.isEmpty(forum.getGuestLogin())
                ? forum.getGuestLogin()
                : requestContext.getUserGuestLoginHint();
    }

    public long getId() {
        return id;
    }

    public void setId(long id) {
        this.id = id;
    }

    public long getParentId() {
        return parentId;
    }

    public void setParentId(long parentId) {
        this.parentId = parentId;
    }

    public String getBody() {
        return body;
    }

    public void setBody(String body) {
        this.body = body;
    }

    public boolean isHidden() {
        return hidden;
    }

    public void setHidden(boolean hidden) {
        this.hidden = hidden;
    }

    public boolean isDisabled() {
        return disabled;
    }

    public void setDisabled(boolean disabled) {
        this.disabled = disabled;
    }

    public short getRelogin() {
        return relogin;
    }

    public void setRelogin(short relogin) {
        this.relogin = relogin;
    }

    public String getGuestLogin() {
        return guestLogin;
    }

    public void setGuestLogin(String guestLogin) {
        this.guestLogin = guestLogin;
    }

    public String getLogin() {
        return login;
    }

    public void setLogin(String login) {
        this.login = login;
    }

    public String getPassword() {
        return password;
    }

    public void setPassword(String password) {
        this.password = password;
    }

    public boolean isRemember() {
        return remember;
    }

    public void setRemember(boolean remember) {
        this.remember = remember;
    }

}
