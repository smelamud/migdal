package ua.org.migdal.helper;

import java.io.IOException;

import javax.inject.Inject;

import com.github.jknack.handlebars.Handlebars;
import com.github.jknack.handlebars.Options;

import ua.org.migdal.data.Editable;
import ua.org.migdal.data.Entry;
import ua.org.migdal.session.RequestContext;

@HelperSource
public class SessionHelperSource {

    @Inject
    private RequestContext requestContext;

    public CharSequence notPrint(Options options) throws IOException {
        StringBuilder buf = new StringBuilder();
        buf.append("<div class=\"hidden-print\">");
        buf.append(options.apply(options.fn));
        buf.append("</div>");
        return buf;
    }

    public CharSequence english(Options options) throws IOException {
        CharSequence result = requestContext.isEnglish() ? options.apply(options.fn) : options.apply(options.inverse);
        Object variableName = options.hash("var", null);
        if (variableName == null) {
            return result;
        }
        result = result instanceof Handlebars.SafeString ? result : new Handlebars.SafeString(result.toString().trim());
        options.data(variableName.toString(), result);
        return "";
    }

    public CharSequence notEnglish(Options options) throws IOException {
        CharSequence result = !requestContext.isEnglish() ? options.apply(options.fn) : options.apply(options.inverse);
        Object variableName = options.hash("var", null);
        if (variableName == null) {
            return result;
        }
        result = result instanceof Handlebars.SafeString ? result : new Handlebars.SafeString(result.toString().trim());
        options.data(variableName.toString(), result);
        return "";
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

    public CharSequence adminTopics(Options options) throws IOException {
        return requestContext.isUserAdminTopics() ? options.apply(options.fn) : options.apply(options.inverse);
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