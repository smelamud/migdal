package ua.org.migdal.helper;

import java.io.IOException;

import com.github.jknack.handlebars.Options;
import org.springframework.beans.factory.annotation.Autowired;
import ua.org.migdal.RequestContext;

@HelperSource
public class SessionHelperSource {

    @Autowired
    private RequestContext requestContext;

    public CharSequence print(Options options) throws IOException {
        return options.<Boolean>get("print") ? options.apply(options.fn) : options.apply(options.inverse);
    }

    public CharSequence notPrint(Options options) throws IOException {
        return !options.<Boolean>get("print") ? options.apply(options.fn) : options.apply(options.inverse);
    }

    public CharSequence english(Options options) throws IOException {
        return options.get("userDomain").equals("english")
                ? options.apply(options.fn)
                : options.apply(options.inverse);
    }

    public CharSequence notEnglish(Options options) throws IOException {
        return !options.get("userDomain").equals("english")
                ? options.apply(options.fn)
                : options.apply(options.inverse);
    }

    public CharSequence logged(Options options) throws IOException {
        return requestContext.isLogged() ? options.apply(options.fn) : options.apply(options.inverse);
    }

    public CharSequence notLogged(Options options) throws IOException {
        return !requestContext.isLogged() ? options.apply(options.fn) : options.apply(options.inverse);
    }

    public CharSequence moderator(Options options) throws IOException {
        return requestContext.isUserModerator() ? options.apply(options.fn) : options.apply(options.inverse);
    }

    public CharSequence adminUsers(Options options) throws IOException {
        return requestContext.isUserAdminUsers() ? options.apply(options.fn) : options.apply(options.inverse);
    }

}