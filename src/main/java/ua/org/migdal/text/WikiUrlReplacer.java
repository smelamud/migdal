package ua.org.migdal.text;

import java.util.regex.Matcher;
import java.util.regex.Pattern;

import org.springframework.util.StringUtils;
import ua.org.migdal.util.Utils;

class WikiUrlReplacer {

    private static final Pattern URL_DELIMITED = Pattern.compile(
            "(^|[\\s.,:;\\(\\)])(([^\\s\\(\\)]+:/)?/[^\\s&;]\\S*[^\\s.,:;\\(\\)&\\\\])");
    private static final Pattern URL_FOLLOWING = Pattern.compile(
            "\\s+((\\S+:/)?/[^\\s&;]\\S*[^\\s.,:;\\(\\)&\\\\])");
    private static final Pattern EMAIL = Pattern.compile(
            "[A-Za-z0-9-_]+(\\.[A-Za-z0-9-_]+)*@[A-Za-z0-9-]+(\\.[A-Za-z0-9-]+)*");

    private enum State {
        REGULAR,
        QUOTE,
        OPEN_QUOTE,
        CLOSE_QUOTE,
        LABELED_URL
    }

    private String text;
    private int start;
    private int end;
    private State state;
    private StringBuilder out = new StringBuilder();

    WikiUrlReplacer(String text) {
        this.text = text;
    }

    private String getUrlTag(String whole, String url, String protocol, String content) {
        if (whole.indexOf('\'') >= 0 || whole.indexOf('<') >= 0 || whole.indexOf('>') >= 0) {
            return whole;
        }
        if (!Utils.isAsciiNoWhitespace(url)) {
            return whole;
        }
        return String.format("<a href=\"%s\" local=\"%s\">%s</a>",
                url,
                StringUtils.isEmpty(protocol) ? "true" : "false",
                content);
    }

    private void goFurther(State targetState) {
        if (end > start) {
            out.append(Utils.replaceAll(text.substring(start, end), URL_DELIMITED,
                    m -> m.group(1) + getUrlTag(m.group(2), m.group(2), m.group(3), m.group(2))));
            start = end;
        }
        state = targetState;
    }

    private String replace() {
        state = State.REGULAR;
        Matcher m = null;
        while (end < text.length()) {
            switch (state) {
                case REGULAR:
                    end = text.indexOf('\'', start);
                    end = end < 0 ? text.length() : end;
                    goFurther(State.QUOTE);
                    break;

                case QUOTE:
                    end = start + 1;
                    if (start != 0 && !WikiText.isDelimiter(text.charAt(start - 1))) {
                        goFurther(State.REGULAR);
                    } else {
                        state = State.OPEN_QUOTE;
                    }
                    break;

                case OPEN_QUOTE:
                    end = text.indexOf('\'', end);
                    end = end < 0 ? text.length() : end + 1;
                    state = State.CLOSE_QUOTE;
                    break;

                case CLOSE_QUOTE:
                    m = URL_FOLLOWING.matcher(text.substring(end));
                    if (!m.lookingAt()) {
                        end--;
                        goFurther(State.REGULAR);
                    } else {
                        state = State.LABELED_URL;
                    }
                    break;

                case LABELED_URL:
                    if (m != null) { // opposite never happens, we check only to avoid compilation error
                        out.append(getUrlTag(m.group(0), m.group(1), m.group(2), text.substring(start + 1, end - 1)));
                        start = end + m.group(0).length();
                    }
                    end = start;
                    state = State.REGULAR;
                    break;
            }
        }
        if (end > start) {
            out.append(text.substring(start, end));
        }
        return EMAIL.matcher(out).replaceAll("<email addr=\"$0\" />");
    }

    public static String replace(String text) {
        return new WikiUrlReplacer(text).replace();
    }

}