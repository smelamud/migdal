package ua.org.migdal.helper;

import javax.inject.Inject;

import org.springframework.util.StringUtils;

import com.github.jknack.handlebars.Handlebars.SafeString;
import com.github.jknack.handlebars.Options;

import ua.org.migdal.data.Vote;
import ua.org.migdal.data.VoteType;
import ua.org.migdal.helper.util.HelperUtils;
import ua.org.migdal.manager.VoteManager;
import ua.org.migdal.session.RequestContext;

@HelperSource
public class VoteHelperSource {

    @Inject
    private RequestContext requestContext;

    @Inject
    private VoteManager voteManager;

    @Inject
    private ImagesHelperSource imagesHelperSource;

    public CharSequence rating(Options options) {
        long value = HelperUtils.intArg("value", options.hash("value"));
        CharSequence style = options.hash("style");
        Object id = options.hash("id", "0");

        return rating(value, style, id);
    }

    CharSequence rating(long value) {
        return rating(value, null, "0");
    }

    CharSequence rating(long value, CharSequence style, Object id) {
        StringBuilder buf = new StringBuilder();
        buf.append("<span class=\"small-rating-");
        HelperUtils.safeAppend(buf, id);
        buf.append(' ');
        if (value == 0) {
            buf.append("small-rating-zero");
        } else if (value > 0) {
            buf.append("small-rating-plus");
        } else {
            buf.append("small-rating-minus");
        }
        buf.append('"');
        if (style != null && style.length() > 0) {
            buf.append(" style=\"");
            buf.append(style);
            buf.append('"');
        }
        buf.append(">(");
        if (value > 0) {
            buf.append('+');
        }
        buf.append(value);
        buf.append(")</span>");
        return new SafeString(buf);
    }

    public CharSequence votePanel(Options options) {
        long id = HelperUtils.intArg("id", HelperUtils.mandatoryHash("id", options));
        long rating = HelperUtils.intArg("rating", HelperUtils.mandatoryHash("rating", options));
        CharSequence align = options.hash("align");

        return votePanel(id, rating, align);
    }

    CharSequence votePanel(long id, long rating, CharSequence align) {
        if (requestContext.isPrintMode() || requestContext.isEnglish()) {
            return "";
        }

        Vote vote = voteManager.findVote(VoteType.VOTE, id);

        StringBuilder buf = new StringBuilder();
        buf.append("<div class=\"vote-panel\"");
        if (!StringUtils.isEmpty(align)) {
            HelperUtils.appendAttr(buf, "style", String.format("float: %s", align));
        }
        buf.append('>');

        if (vote == null) {
            if (requestContext.isLogged()) {
                String title = "Неинтересно, плохо написано";
                String klass = String.format("vote-minus-%d vote-button vote-active", id);
                buf.append(imagesHelperSource.image("/pics/vote-minus.gif", title, title, null, klass, id, 2, null));
            } else {
                String title = "Чтобы ставить отрицательные оценки, нужно зарегистрироваться";
                String klass = String.format("vote-minus-%d vote-button", id);
                buf.append(imagesHelperSource.image("/pics/vote-minus-gray.gif", title, title, null, klass));
            }
        } else {
            if (vote.getVote() >= 0 && vote.getVote() < 3) {
                buf.append(imagesHelperSource.image("/pics/vote-minus-gray.gif", null, null, null, "vote-button"));
            } else {
                buf.append("<div class=\"vote-button\">&nbsp;</div>");
            }
        }

        {
            String klass;
            String ratingS;
            if (rating == 0) {
                klass = "rating-zero";
                ratingS = "0";
            } else if (rating > 0) {
                klass = "rating-plus";
                ratingS = String.format("+%d", rating);
            } else {
                klass = "rating-minus";
                ratingS = Long.toString(rating);
            }
            buf.append("<div");
            HelperUtils.appendAttr(buf, "class", String.format("rating-%d %s", id, klass));
            buf.append('>');
            buf.append(ratingS);
            buf.append("</div>");
        }

        if (vote == null) {
            String title = "Интересно, хорошо написано";
            String klass = String.format("vote-plus-%d vote-button vote-active", id);
            buf.append(imagesHelperSource.image("/pics/vote-plus.gif", title, title, null, klass, id, 4, null));
        } else {
            if (vote.getVote() > 3) {
                buf.append(imagesHelperSource.image("/pics/vote-plus-gray.gif", null, null, null, "vote-button"));
            } else {
                buf.append("<div class=\"vote-button\">&nbsp;</div>");
            }
        }

        buf.append("</div>");
        return new SafeString(buf);
    }

}