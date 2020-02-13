package ua.org.migdal.text;

import java.util.regex.Pattern;

import org.springframework.util.StringUtils;
import ua.org.migdal.mtext.MtextFormat;
import ua.org.migdal.util.Utils;
import ua.org.migdal.util.XmlUtils;

public class WikiText {

    private static final Pattern CENTER = Pattern.compile("(^|\\n)[^\\S\\n]{10}[^\\S\\n]*([^\\n]+)(\\n|$)");
    private static final Pattern SPACES = Pattern.compile("\\s*");
    private static final Pattern FOOTNOTE = Pattern.compile(
            "(?:^|(?<=>)|\\s+)(?:'([^']+)'\\s)?\\{\\{((?:[^}]|}[^}])+)}}");

    public static boolean isSpace(char c) {
        switch (Character.getType(c)) {
            case Character.COMBINING_SPACING_MARK:
            case Character.CONTROL:
            case Character.ENCLOSING_MARK:
            case Character.FORMAT:
            case Character.LINE_SEPARATOR:
            case Character.NON_SPACING_MARK:
            case Character.PARAGRAPH_SEPARATOR:
            case Character.PRIVATE_USE:
            case Character.SPACE_SEPARATOR:
            case Character.SURROGATE:
            case Character.UNASSIGNED:
                return true;
        }
        return false;
    }

    public static boolean isPunctuation(char c) {
        switch (Character.getType(c)) {
            case Character.CONNECTOR_PUNCTUATION:
            case Character.DASH_PUNCTUATION:
            case Character.ENCLOSING_MARK:
            case Character.END_PUNCTUATION:
            case Character.FINAL_QUOTE_PUNCTUATION:
            case Character.INITIAL_QUOTE_PUNCTUATION:
            case Character.OTHER_PUNCTUATION:
            case Character.START_PUNCTUATION:
            case Character.MATH_SYMBOL:
                return true;
        }
        return false;
    }

    public static boolean isDelimiter(char c) {
        return isSpace(c) || isPunctuation(c);
    }

    private static String flipReplace(char c, String startTag, String endTag, String s) {
        return flipReplace(c, startTag, endTag, s, true);
    }

    private static String flipReplace(char c, String startTag, String endTag, String s, boolean delimited) {
        StringBuilder buf = new StringBuilder();
        boolean tag = false;
        int inTag = 0;
        for (int n = 0; n < s.length(); n++) {
            if (s.charAt(n) == '<') {
                inTag++;
            }
            if (s.charAt(n) == '>' && (n == s.length() - 1 || s.charAt(n + 1) != '>') && s.charAt(n - 1) != '>') {
                if (inTag > 0) {
                    inTag--;
                }
            }
            if (inTag == 0 && !tag && s.charAt(n) == c
                    && (n == 0 || (!delimited || isDelimiter(s.charAt(n - 1)))
                                  && s.charAt(n - 1) != '&' /* &#entity; combinations are not replaced */
                                  && s.charAt(n - 1) != c)
                    && n != s.length() - 1
                    && (!delimited || !isDelimiter(s.charAt(n + 1))
                        || s.charAt(n + 1) == '&'
                        || s.charAt(n + 1) == '=' || s.charAt(n + 1) == '~'
                        || s.charAt(n + 1) == '-' || s.charAt(n + 1) == '<'
                        || s.charAt(n + 1) == '(' || s.charAt(n + 1) == '['
                        || Character.getType(s.charAt(n + 1)) == Character.INITIAL_QUOTE_PUNCTUATION)
                    /* word may start by entity or font
                     * style markup or by dash or by tag
                     * or by parenthesis or by bracket
                     */
                    && s.charAt(n + 1) != c) {
                buf.append(startTag);
                tag = true;
            } else if (inTag == 0 && tag && s.charAt(n) == c
                        && (n == s.length() - 1 || (!delimited || isDelimiter(s.charAt(n + 1))))
                        && n != 0 && (!delimited || !isSpace(s.charAt(n - 1)))
                        /* final punctuation is part of the word */) {
                buf.append(endTag);
                tag = false;
            } else {
                buf.append(s.charAt(n));
            }
        }
        if (tag) {
            buf.append(endTag);
        }
        return buf.toString();
    }

    private static String replaceUrls(String s) {
        StringBuilder buf = new StringBuilder();
        int start = 0;
        while (start < s.length()) {
            int end = s.indexOf('<', start);
            end = end < 0 ? s.length() : end;
            buf.append(WikiUrlReplacer.replace(s.substring(start, end)));
            if (end >= s.length()) {
                break;
            }
            start = s.indexOf('>', end);
            start = start < 0 ? s.length() - 1 : start;
            buf.append(s.substring(end, start + 1));
            start++;
        }
        return buf.toString();
    }

    private static int quoteLevel(String s) {
        int n = 0;
        for (int i = 0; i < s.length(); i++) {
            if (isSpace(s.charAt(i))) {
                continue;
            }
            if (s.charAt(i) == '>') {
                n++;
                continue;
            }
            break;
        }
        return n;
    }

    private static String properQuoting(String s) {
        StringBuilder buf = new StringBuilder();
        int i = 0;
        while (i < s.length()) {
            if (isSpace(s.charAt(i))) {
                i++;
                continue;
            }
            if (s.charAt(i) == '>') {
                buf.append("> ");
                i++;
                continue;
            }
            break;
        }
        buf.append(s.substring(i));
        return buf.toString();
    }

    private static String replaceQuoting(String s) {
        String[] lines = s.split("\n");
        int level = 0;
        for (int i = 0; i < lines.length; i++) {
            lines[i] = properQuoting(lines[i]);
            int l = quoteLevel(lines[i]);
            lines[i] = lines[i].substring(2 * l);
            if (l >= level) {
                StringBuilder buf = new StringBuilder();
                for (int j = 0; j < l - level; j++) {
                    buf.append("<quote>");
                }
                buf.append(lines[i]);
                lines[i] = buf.toString();
            } else {
                StringBuilder buf = new StringBuilder(lines[i - 1]);
                for (int j = 0; j < level - l; j++) {
                    buf.append("</quote>");
                }
                lines[i - 1] = buf.toString();
            }
            level = l;
        }
        return String.join("\n", lines);
    }

    private static String replaceCenter(String s) {
        return CENTER.matcher(s).replaceAll("$1<center>$2</center>$3");
    }

    private static boolean isRuler(String s, char c) {
        s = s.trim();
        for (int i = 0; i < s.length(); i++) {
            if (s.charAt(i) != c) {
                return false;
            }
        }
        return s.length() >= 3;
    }

    private static String replaceHeading(String s, int level, char c) {
        StringBuilder out = new StringBuilder();
        String[] lines = s.split("\n");
        int i = 0;
        while (i < lines.length) {
            if (i + 1 < lines.length) {
                // Reverse ligature
                String next = lines[i + 1]
                                .replace("&#8212;", "---")
                                .replace("&#x2014;", "---")
                                .replace("\u2014", "---");
                if (isRuler(next, c) && !SPACES.matcher(lines[i]).matches()) {
                    out.append("<h");
                    out.append(level);
                    out.append('>');
                    out.append(lines[i]);
                    out.append("</h");
                    out.append(level);
                    out.append('>');
                    i++;
                } else {
                    out.append(lines[i]);
                }
            } else {
                out.append(lines[i]);
            }
            out.append('\n');
            i++;
        }
        return out.toString();
    }

    private static String replaceHeadings(String s) {
        return replaceHeading(
                    replaceHeading(
                        replaceHeading(s, 2, '='),
                    3, '-'),
               4, '~');
    }

    private static String replaceFootnotes(String s) {
        return Utils.replaceAll(s, FOOTNOTE, m -> {
            if (StringUtils.isEmpty(m.group(1))) {
                return String.format("<footnote>%s</footnote>", m.group(2));
            } else {
                return String.format("<footnote title=\"%s\">%s</footnote>", m.group(1), m.group(2));
            }
        });
    }

    public static String convert(String text, TextFormat format, MtextFormat destFormat) {
        String s = XmlCleaner.cleanup(text);
        switch(format) {
            default:
            case MAIL:
                if (destFormat.atLeast(MtextFormat.SHORT)) {
                    s = replaceQuoting(s);
                }
                /* fall through */

            case PLAIN:
                s = replaceUrls(s);
                if (destFormat.atLeast(MtextFormat.SHORT)) {
                    s = replaceHeadings(s);
                    s = replaceCenter(s);
                    s = XmlText.replaceParagraphs(s);
                }
                if (destFormat.atLeast(MtextFormat.LONG)) {
                    s = replaceFootnotes(s);
                }
                s = s.replace("\r", "").replace("\n", "<br />");
                s = s.replace("\\\\", "<br />");
                s = flipReplace('_', "<u>", "</u>", s);
                s = flipReplace('~', "<b>", "</b>", s);
                s = flipReplace('=', "<i>", "</i>", s);
                s = flipReplace('^', "<sup>", "</sup>", s, false);
                s = flipReplace('#', "<tt>", "</tt>", s);
                break;

            case TEX:
                s = replaceUrls(s);
                if (destFormat.atLeast(MtextFormat.SHORT)) {
                    s = replaceHeadings(s);
                    s = replaceCenter(s);
                    s = XmlText.replaceParagraphs(s);
                }
                if (destFormat.atLeast(MtextFormat.LONG)) {
                    s = replaceFootnotes(s);
                }
                s = s.replace("\\\\", "<br />");
                s = flipReplace('_', "<u>", "</u>", s);
                s = flipReplace('~', "<b>", "</b>", s);
                s = flipReplace('=', "<i>", "</i>", s);
                s = flipReplace('^', "<sup>", "</sup>", s, false);
                s = flipReplace('#', "<tt>", "</tt>", s);
                break;
        }
        return XmlUtils.delicateAmps(XmlCleaner.cleanup(s)).toString();
    }

}