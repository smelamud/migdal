package ua.org.migdal.helper;

import org.springframework.beans.factory.annotation.Autowired;

import com.github.jknack.handlebars.Handlebars;

import ua.org.migdal.data.User;

@HelperSource
public class UsersHelperSource {

    @Autowired
    private StringHelperSource stringHelperSource;

    public CharSequence mailToLink(User user) {
        if (!user.isEmailVisible()) {
            return "";
        }
        StringBuilder buf = new StringBuilder();
        buf.append("<a href=\"mailto:");
        buf.append(stringHelperSource.ue(user.getEmail()));
        buf.append("\" title=\"Написать письмо\">");
        buf.append(stringHelperSource.he(user.getEmail()));
        buf.append("</a>");
        return new Handlebars.SafeString(buf.toString());
    }

}
