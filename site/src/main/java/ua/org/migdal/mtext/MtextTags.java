package ua.org.migdal.mtext;

import java.util.Collections;
import java.util.HashMap;
import java.util.HashSet;
import java.util.Map;
import java.util.Set;

public class MtextTags {

    private static final Map<String, MtextFormat> TAGS;
    private static final Set<String> EMPTY_TAGS;
    private static final Set<String> CHAPTER_TAGS;

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
        tags.put("span",        MtextFormat.LINE);
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
        tags.put("div",         MtextFormat.LONG);
        TAGS = Collections.unmodifiableMap(tags);

        Set<String> emptyTags = new HashSet<>();
        emptyTags.add("email");
        emptyTags.add("user");
        emptyTags.add("br");
        emptyTags.add("img");
        emptyTags.add("param");
        EMPTY_TAGS = Collections.unmodifiableSet(emptyTags);

        Set<String> chapterTags = new HashSet<>();
        chapterTags.add("quote");
        chapterTags.add("footnote");
        chapterTags.add("table");
        chapterTags.add("tr");
        chapterTags.add("td");
        chapterTags.add("th");
        chapterTags.add("incut");
        chapterTags.add("div");
        CHAPTER_TAGS = Collections.unmodifiableSet(chapterTags);
    }

    public static boolean isAllowed(String tag) {
        return TAGS.containsKey(tag);
    }

    public static boolean isEmpty(String tag) {
        return EMPTY_TAGS.contains(tag);
    }

    public static MtextFormat getLevel(String tag) {
        return TAGS.get(tag);
    }

    public static boolean isChapter(String tag) {
        return CHAPTER_TAGS.contains(tag);
    }

}
