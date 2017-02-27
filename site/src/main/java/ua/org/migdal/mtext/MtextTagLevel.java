package ua.org.migdal.mtext;

import java.util.Collections;
import java.util.HashMap;
import java.util.Map;

public class MtextTagLevel {

    public final static Map<String, MtextFormat> TAGS;

    static {
        Map<String, MtextFormat> tags = new HashMap<>();
        tags.put("mtext-line",  MtextFormat.LINE);
        tags.put("a",           MtextFormat.LINE);
        tags.put("email",       MtextFormat.LINE);
        tags.put("user",        MtextFormat.LINE);
        tags.put("img",         MtextFormat.LINE);
        tags.put("b",           MtextFormat.LINE);
        tags.put("i",           MtextFormat.LINE);
        tags.put("u",           MtextFormat.LINE);
        tags.put("s",           MtextFormat.LINE);
        tags.put("strike",      MtextFormat.LINE);
        tags.put("tt",          MtextFormat.LINE);
        tags.put("sup",         MtextFormat.LINE);
        tags.put("mtext-short", MtextFormat.SHORT);
        tags.put("p",           MtextFormat.SHORT);
        tags.put("quote",       MtextFormat.SHORT);
        tags.put("center",      MtextFormat.SHORT);
        tags.put("h2",          MtextFormat.SHORT);
        tags.put("h3",          MtextFormat.SHORT);
        tags.put("h4",          MtextFormat.SHORT);
        tags.put("br",          MtextFormat.SHORT);
        tags.put("ul",          MtextFormat.SHORT);
        tags.put("ol",          MtextFormat.SHORT);
        tags.put("dl",          MtextFormat.SHORT);
        tags.put("li",          MtextFormat.SHORT);
        tags.put("object",      MtextFormat.SHORT);
        tags.put("param",       MtextFormat.SHORT);
        tags.put("embed",       MtextFormat.SHORT);
        tags.put("mtext-long",  MtextFormat.LONG);
        tags.put("footnote",    MtextFormat.LONG);
        tags.put("table",       MtextFormat.LONG);
        tags.put("tr",          MtextFormat.LONG);
        tags.put("td",          MtextFormat.LONG);
        tags.put("th",          MtextFormat.LONG);
        tags.put("incut",       MtextFormat.LONG);
        TAGS = Collections.unmodifiableMap(tags);
    }

}
