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

}