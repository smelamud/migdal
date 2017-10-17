package ua.org.migdal.helper;

import javax.inject.Inject;

import com.github.jknack.handlebars.Handlebars;
import com.github.jknack.handlebars.Options;
import ua.org.migdal.Config;
import ua.org.migdal.manager.ReloginVariant;
import ua.org.migdal.helper.util.HelperUtils;
import ua.org.migdal.session.RequestContext;

@HelperSource
public class ReloginHelperSource {

    @Inject
    private Config config;

    @Inject
    private FormsHelperSource formsHelperSource;

    @Inject
    private RequestContext requestContext;

    public CharSequence formRelogin(Options options) {
        boolean noGuests = !config.isAllowGuests() || HelperUtils.boolArg(options.hash("noGuests"));
        long relogin = HelperUtils.intArg("relogin", options.hash("relogin"));
        CharSequence guestLogin = options.hash("guestLogin", "");
        CharSequence login = options.hash("login", "");
        boolean remember = HelperUtils.boolArg(options.hash("remember"));

        ReloginVariant reloginVariant;
        if (relogin == 0) {
            if (requestContext.isLogged()) {
                reloginVariant = ReloginVariant.SAME;
            } else {
                reloginVariant = noGuests ? ReloginVariant.LOGIN : ReloginVariant.GUEST;
            }
        } else {
            reloginVariant = ReloginVariant.valueOf((int) relogin);
        }

        StringBuilder buf = new StringBuilder();
        buf.append(formsHelperSource.formLineBegin("Подписать сообщение как", "relogin",
                                                   false, null, null, options));
        buf.append("<div class=\"relogin\">");
        if (!noGuests) {
            buf.append("<label>");
            buf.append(formsHelperSource.radioButton("relogin", ReloginVariant.GUEST.getValue(),
                                                     reloginVariant == ReloginVariant.GUEST,
                                                     "relogin-guest", null));
            buf.append("&nbsp;Имя&nbsp;");
            buf.append(formsHelperSource.edit("guestLogin", guestLogin, "15", "35", null));
            buf.append("&nbsp;без пароля (гостевой вход)</label>");
        }
        buf.append("<br>");
        if (requestContext.isLogged()) {
            buf.append("<label>");
            buf.append(formsHelperSource.radioButton("relogin", ReloginVariant.SAME.getValue(),
                                                     reloginVariant == ReloginVariant.SAME, null,
                                                     null));
            buf.append("&nbsp;Зарегистрированный пользователь&nbsp;<b><a href=\"/users/");
            buf.append(HelperUtils.ue(requestContext.getUserFolder()));
            buf.append("/\">");
            HelperUtils.safeAppend(buf, requestContext.getUserLogin());
            buf.append("</a></b></label>");
            buf.append("<br>");
        }
        buf.append("<label>");
        if (!noGuests || requestContext.isLogged()) {
            buf.append(formsHelperSource.radioButton("relogin", ReloginVariant.LOGIN.getValue(),
                                                     reloginVariant == ReloginVariant.LOGIN, null,
                                                     null));
        }
        buf.append("&nbsp;Ник&nbsp;");
        buf.append(formsHelperSource.edit("login", login, "15", "30", null));
        buf.append("&nbsp;Пароль&nbsp;");
        buf.append("<input type=\"password\" name=\"password\" size=15 maxlength=30>");
        buf.append("&nbsp;Войти?&nbsp;");
        buf.append(formsHelperSource.checkboxButton("remember", 1, remember, null, null));
        buf.append("</label>");
        buf.append("<br>");
        buf.append("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
        buf.append("<a href=\"/register/?back=");
        buf.append(HelperUtils.ue(requestContext.getLocation()));
        buf.append("\">Зарегистрироваться</a>&nbsp;&nbsp;<a href=\"/recall-password/?back=");
        buf.append(HelperUtils.ue(requestContext.getLocation()));
        buf.append("\">Забыли пароль?</a>");
        buf.append("</div>");
        buf.append(formsHelperSource.formLineEnd());
        return new Handlebars.SafeString(buf);
    }

}