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

    private String text;
    private int start;
    private int end;
    private int state;
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

    private void goFurther(int targetState) {
        if (end > start) {
            out.append(Utils.replaceAll(text.substring(start, end), URL_DELIMITED,
                    (m) -> m.group(1) + getUrlTag(m.group(2), m.group(2), m.group(3), m.group(2))));
            start = end;
        }
        state = targetState;
    }

    private String replace() {
        Matcher m = null;
        while (end < text.length()) {
            switch (state) {
                case 0:
                    end = text.indexOf('\'', start);
                    end = end < 0 ? text.length() : end;
                    goFurther(1);
                    break;

                case 1:
                    end = start + 1;
                    if (start != 0 && !WikiText.isDelimiter(text.charAt(start - 1))) {
                        goFurther(0);
                    } else {
                        state = 2;
                    }
                    break;

                case 2:
                    end = text.indexOf('\'', end);
                    end = end < 0 ? text.length() : end + 1;
                    state = 3;
                    break;

                case 3:
                    m = URL_FOLLOWING.matcher(text.substring(end));
                    if (!m.lookingAt()) {
                        end--;
                        goFurther(0);
                    } else {
                        state = 4;
                    }
                    break;

                case 4:
                    if (m != null) { // opposite never happens, we check only to avoid compilation error
                        out.append(getUrlTag(m.group(0), m.group(1), m.group(2), text.substring(start + 1, end - 1)));
                        start = end + m.group(0).length();
                    }
                    end = start;
                    state = 0;
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