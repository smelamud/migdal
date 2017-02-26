package ua.org.migdal.mtext;

import java.util.Collections;
import java.util.HashMap;
import java.util.Map;

public class MtextTagLevel {

    public final static Map<String, MtextFormat> TAGS;

    static {
        Map<String, MtextFormat> tags = new HashMap<>();
        tags.put("MTEXT-LINE", MtextFormat.LINE);
        tags.put("A", MtextFormat.LINE);
        tags.put("EMAIL", MtextFormat.LINE);
        tags.put("USER", MtextFormat.LINE);
        tags.put("IMG", MtextFormat.LINE);
        tags.put("B", MtextFormat.LINE);
        tags.put("I", MtextFormat.LINE);
        tags.put("U", MtextFormat.LINE);
        tags.put("S", MtextFormat.LINE);
        tags.put("STRIKE", MtextFormat.LINE);
        tags.put("TT", MtextFormat.LINE);
        tags.put("SUP", MtextFormat.LINE);
        tags.put("MTEXT-SHORT", MtextFormat.SHORT);
        tags.put("P", MtextFormat.SHORT);
        tags.put("QUOTE", MtextFormat.SHORT);
        tags.put("CENTER", MtextFormat.SHORT);
        tags.put("H2", MtextFormat.SHORT);
        tags.put("H3", MtextFormat.SHORT);
        tags.put("H4", MtextFormat.SHORT);
        tags.put("BR", MtextFormat.SHORT);
        tags.put("UL", MtextFormat.SHORT);
        tags.put("OL", MtextFormat.SHORT);
        tags.put("DL", MtextFormat.SHORT);
        tags.put("LI", MtextFormat.SHORT);
        tags.put("OBJECT", MtextFormat.SHORT);
        tags.put("PARAM", MtextFormat.SHORT);
        tags.put("EMBED", MtextFormat.SHORT);
        tags.put("MTEXT-LONG", MtextFormat.LONG);
        tags.put("FOOTNOTE", MtextFormat.LONG);
        tags.put("TABLE", MtextFormat.LONG);
        tags.put("TR", MtextFormat.LONG);
        tags.put("TD", MtextFormat.LONG);
        tags.put("TH", MtextFormat.LONG);
        tags.put("INCUT", MtextFormat.LONG);
        TAGS = Collections.unmodifiableMap(tags);
    }

}
