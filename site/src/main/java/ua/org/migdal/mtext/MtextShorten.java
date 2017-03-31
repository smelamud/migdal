package ua.org.migdal.mtext;

import java.util.ArrayList;
import java.util.List;

import ua.org.migdal.util.XmlConverter;
import ua.org.migdal.util.XmlUtils;
import ua.org.migdal.util.exception.XmlConverterException;

class MtextShorten {

    private static final String[][] ENDS = {
            {"\u001F"},
            {". ", "! ", "? ", ".\n", "!\n", "?\n"},
            {": ", ", ", "; ", ") ", ":\n", ",\n", ";\n", ")\n"}
    };

    private static int indexOfAll(String haystack, String needle, List<Integer> found) {
        int pos = haystack.indexOf(needle);
        while (pos >= 0) {
            found.add(pos);
            pos = haystack.indexOf(needle, pos + 1);
        }
        return found.size();
    }

    private static int indexOfAll(String haystack, String[] needles, List<Integer> found) {
        for (String needle : needles) {
            indexOfAll(haystack, needle, found);
        }
        return found.size();
    }

    private static int getShortenLength(String s, int len, int mdlen, int pdlen) {
        if (s.length() <= len + pdlen) {
            return s.length();
        }
        int st = len - mdlen;
        st = st < 0 ? 0 : st;
        String c = s.substring(st, mdlen + pdlen);
        for (String[] patterns : ENDS) {
            List<Integer> matches = new ArrayList<>();
            if (indexOfAll(c, patterns, matches) <= 0) {
                continue;
            }
            int bestpos = -1;
            for (int pos : matches) {
                if (bestpos < 0 || Math.abs(bestpos - mdlen) > Math.abs(pos - mdlen)) {
                    bestpos = pos;
                }
            }
            int matchLen = patterns.length > 1 ? 2 : 1;
            return bestpos + st + matchLen;
        }
        return len;
    }

    private static int cleanLength(String s) throws XmlConverterException {
        if (!XmlUtils.hasMarkup(s)) {
            return s.length();
        }
        MtextToLine handler = new MtextToLine();
        XmlConverter.convert(s, handler);
        return handler.getLine().length();
    }

    public static String shorten(String s, int len, int mdlen, int pdlen) {
        return shorten(s, len, mdlen, pdlen, false, "");
    }

    public static String shorten(String s, int len, int mdlen, int pdlen, boolean clearTags, String suffix) {
        boolean hasMarkup = XmlUtils.hasMarkup(s);

        try {
            String line;
            if (hasMarkup) {
                MtextToLine handler = new MtextToLine();
                XmlConverter.convert(s, handler);
                line = handler.getLine().toString();
            } else {
                line = s;
            }

            int n = getShortenLength(line, len, mdlen, pdlen);
            String c = n >= line.length() ? "" : suffix;

            String result;
            if (hasMarkup) {
                MtextToLength handler = new MtextToLength(n, clearTags);
                XmlConverter.convert(s, handler);
                result = handler.getShortened().toString();
            } else {
                result = s.substring(0, n);
            }
            return XmlUtils.delicateAmps(result) + c;
        } catch (XmlConverterException e) {
            return String.format("<b>** %s: %s **</b>", e.getMessage(), e.getCause().getMessage());
        }
    }

}