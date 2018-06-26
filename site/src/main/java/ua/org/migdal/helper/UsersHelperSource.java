package ua.org.migdal.helper;

import javax.inject.Inject;

import com.github.jknack.handlebars.Handlebars.SafeString;

import ua.org.migdal.data.User;
import ua.org.migdal.helper.calendar.Formatter;
import ua.org.migdal.helper.util.HelperUtils;
import ua.org.migdal.manager.UserManager;

@HelperSource
public class UsersHelperSource {

    @Inject
    private UserManager userManager;

    public CharSequence userNameLink(String userName, String guestName) {
        return userLink(userName != null ? userManager.getByLogin(userName) : null, guestName);
    }

    CharSequence userLink(User user, String guestName) {
        StringBuilder buf = new StringBuilder();
        if (user != null && !user.isGuest()) {
            if (user.isVisible()) {
                buf.append("<a class=\"name\" href=\"/users/");
                buf.append(HelperUtils.ue(user.getFolder()));
                buf.append("/\"");
                HelperUtils.appendAttr(buf, "data-id", user.getId());
                buf.append('>');
                buf.append(HelperUtils.he(user.getLogin()));
                buf.append("</a>");
            } else {
                buf.append("<span class=\"name\">");
                buf.append(HelperUtils.he(user.getLogin()));
                buf.append("</span>");
            }
        } else {
            buf.append("<span class=\"guest-name\">");
            buf.append(HelperUtils.he(guestName));
            buf.append("</span>");
        }
        return new SafeString(buf);
    }

    public CharSequence mailToLink(User user) {
        if (!user.isEmailVisible()) {
            return "";
        }
        StringBuilder buf = new StringBuilder();
        buf.append("<a href=\"mailto:");
        buf.append(HelperUtils.ue(user.getEmail()));
        buf.append("\" title=\"Написать письмо\">");
        HelperUtils.safeAppend(buf, user.getEmail());
        buf.append("</a>");
        return new SafeString(buf);
    }

    public CharSequence lastOnline(User user) {
        if (user.isTooOld()) {
            return "очень давно";
        } else {
            return Formatter.formatFuzzyTimeElapsed(user.getLastOnline().toLocalDateTime());
        }
    }

}