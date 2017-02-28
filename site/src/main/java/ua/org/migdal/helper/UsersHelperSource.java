package ua.org.migdal.helper;

import com.github.jknack.handlebars.Handlebars.SafeString;

import ua.org.migdal.data.User;
import ua.org.migdal.helper.calendar.Formatter;

@HelperSource
public class UsersHelperSource {

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