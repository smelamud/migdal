package ua.org.migdal.helper;

import java.io.IOException;

import javax.inject.Inject;

import com.github.jknack.handlebars.Options;

import ua.org.migdal.data.Editable;
import ua.org.migdal.data.Entry;
import ua.org.migdal.session.RequestContext;

@HelperSource
public class SessionHelperSource {

    @Inject
    private RequestContext requestContext;

    public CharSequence print(Options options) throws IOException {
        return requestContext.isPrintMode() ? options.apply(options.fn) : options.apply(options.inverse);
    }

    public CharSequence notPrint(Options options) throws IOException {
        return !requestContext.isPrintMode() ? options.apply(options.fn) : options.apply(options.inverse);
    }

    public CharSequence english(Options options) throws IOException {
        return requestContext.isEnglish() ? options.apply(options.fn) : options.apply(options.inverse);
    }

    public CharSequence notEnglish(Options options) throws IOException {
        return !requestContext.isEnglish() ? options.apply(options.fn) : options.apply(options.inverse);
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

    public CharSequence editable(Editable object, Options options) throws IOException {
        return object != null && object.isEditable(requestContext)
                ? options.apply(options.fn)
                : options.apply(options.inverse);
    }

    public CharSequence readable(Entry entry, Options options) throws IOException {
        return entry != null && entry.isReadable()
                ? options.apply(options.fn)
                : options.apply(options.inverse);
    }

    public CharSequence writable(Entry entry, Options options) throws IOException {
        return entry != null && entry.isWritable()
                ? options.apply(options.fn)
                : options.apply(options.inverse);
    }

    public CharSequence appendable(Entry entry, Options options) throws IOException {
        return entry != null && entry.isAppendable()
                ? options.apply(options.fn)
                : options.apply(options.inverse);
    }

    public CharSequence postable(Entry entry, Options options) throws IOException {
        return entry != null && entry.isPostable()
                ? options.apply(options.fn)
                : options.apply(options.inverse);
    }

}