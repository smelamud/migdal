package ua.org.migdal.form;

import java.io.Serializable;
import javax.validation.constraints.NotBlank;
import javax.validation.constraints.Size;

import org.springframework.util.StringUtils;
import ua.org.migdal.data.Comment;
import ua.org.migdal.data.Posting;
import ua.org.migdal.manager.SpamManager;
import ua.org.migdal.mtext.MtextFormat;
import ua.org.migdal.session.RequestContext;
import ua.org.migdal.text.Text;
import ua.org.migdal.text.TextFormat;
import ua.org.migdal.util.Perm;

public class CommentForm implements Serializable {

    private static final long serialVersionUID = -163740823595098849L;

    private long id;

    private long parentId;

    @NotBlank
    @Size(max = 4096)
    private String body = "";

    private boolean hidden;

    private boolean disabled;

    private short relogin;

    @Size(max = 30)
    private String guestLogin = "";

    @Size(max = 30)
    private String login = "";

    @Size(max = 40)
    private String password = "";

    private boolean remember;

    @Size(max = 512)
    private String captchaResponse;

    public CommentForm() {
    }

    public CommentForm(Posting posting, RequestContext requestContext) {
        this(new Comment(posting, requestContext), requestContext);
    }

    public CommentForm(Comment comment, RequestContext requestContext) {
        if (comment == null) {
            return;
        }

        id = comment.getId();
        parentId = comment.getParentId();
        body = comment.getBody();
        hidden = comment.isHidden();
        disabled = comment.isDisabled();
        guestLogin = !StringUtils.isEmpty(comment.getGuestLogin())
                ? comment.getGuestLogin()
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

    public String getCaptchaResponse() {
        return captchaResponse;
    }

    public void setCaptchaResponse(String captchaResponse) {
        this.captchaResponse = captchaResponse;
    }

    public boolean isSpam(SpamManager spamManager) {
        return spamManager.isSpam(getBody());
    }

    public void toComment(Comment comment, Posting parent, RequestContext requestContext) {
        comment.setParent(parent);
        comment.setBody(Text.convertLigatures(getBody()));
        comment.setBodyXml(Text.convert(comment.getBody(), TextFormat.MAIL, MtextFormat.SHORT));
        comment.setGuestLogin(getGuestLogin());
        if (isHidden()) {
            comment.setPerms(comment.getPerms() & ~(Perm.OR | Perm.ER));
        } else {
            comment.setPerms(comment.getPerms() | Perm.OR | Perm.ER);
        }
        if (requestContext.isUserModerator()) {
            comment.setDisabled(isDisabled());
        }
    }

    public boolean isTrackChanged(Comment comment) {
        return getId() > 0 && getParentId() != comment.getParentId();
    }

    public boolean isCatalogChanged(Comment comment) {
        return getId() > 0 && getParentId() != comment.getParentId();
    }

}
