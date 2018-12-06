package ua.org.migdal.text;

import ua.org.migdal.mtext.MtextFormat;

public class Text {

    public static String convert(String text, TextFormat format, MtextFormat destFormat) {
        if (format == TextFormat.XML) {
            return XmlText.convert(text, destFormat);
        } else {
            return WikiText.convert(text, format, destFormat);
        }
    }

    public static String convertLigatures(String s) {
        return s.replace("<<", "\u00ab")
                .replace(">>", "\u00bb")
                .replace("---", "\u2014")
                .replace("``", "\u201c")
                .replace("''", "\u201d")
                .replace("(c)", "\u00a9")
                .replace("(C)", "\u00a9")
                .replace("(r)", "\u00ae")
                .replace("(R)", "\u00ae")
                .replace("(tm)", "\u2122")
                .replace("(TM)", "\u2122")
                .replace("No.", "\u2116");
    }

}