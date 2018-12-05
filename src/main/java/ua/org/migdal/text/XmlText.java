package ua.org.migdal.text;

import java.util.regex.Pattern;

import ua.org.migdal.mtext.MtextFormat;
import ua.org.migdal.util.XmlUtils;

public class XmlText {

    private static final Pattern EMPTY_LINE = Pattern.compile("\n\\s*\n");
    private static final String ARROW_LEFT = "&lt;-";
    private static final Pattern PARAGRAPH_LEFT = Pattern.compile("<p>" + ARROW_LEFT);

    public static String replaceParagraphs(String s) {
        s = EMPTY_LINE.matcher(s).replaceAll("</p><p>");
        s = PARAGRAPH_LEFT.matcher(s).replaceAll("<p clear=\"left\">");
        if (s.startsWith(ARROW_LEFT)) {
            return String.format("<p clear=\"left\">%s</p>", s.substring(ARROW_LEFT.length()));
        } else {
            return String.format("<p>%s</p>", s);
        }
    }

    public static String convert(String text, MtextFormat destFormat) {
        String s = text;
        if (destFormat.atLeast(MtextFormat.SHORT)) {
            s = replaceParagraphs(s);
        }
        return XmlUtils.delicateAmps(XmlCleaner.cleanup(s)).toString();
    }

}