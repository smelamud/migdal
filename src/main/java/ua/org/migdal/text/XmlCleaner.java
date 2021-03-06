package ua.org.migdal.text;

import java.util.ArrayDeque;
import java.util.Deque;
import java.util.regex.Matcher;
import java.util.regex.Pattern;
import java.util.stream.Collectors;

import org.springframework.util.StringUtils;
import ua.org.migdal.mtext.MtextTags;
import ua.org.migdal.util.XmlUtils;

class XmlCleaner {

    private static final Pattern TAG = Pattern.compile("<\\s*(/?\\w+)\\s*(.*)>");
    private static final Pattern ATTRIBUTE_QUOTED = Pattern.compile("([-\\w]+)\\s*=\\s*(\"[^\"]*\"|'[^']*')\\s*(.*)");
    private static final Pattern ATTRIBUTE_SIMPLE = Pattern.compile("([-\\w]+)\\s*=\\s*(\\S+)\\s*(.*)");
    private static final Pattern ATTRIBUTE_FLAG = Pattern.compile("([-\\w]+)\\s*(.*)");

    private static class Slice {

        private StringBuilder text;

        Slice(CharSequence text) {
            this.text = new StringBuilder(text);
        }

        public void appendText(CharSequence text) {
            this.text.append(text);
        }

        @Override
        public String toString() {
            return text.toString();
        }

    }

    private static class Tag extends Slice {

        private String name;

        Tag(String name, CharSequence text) {
            super(text);
            this.name = name.toLowerCase();
        }

        Tag(String name, boolean isClosing) {
            this(!isClosing ? name : '/' + name, String.format(!isClosing ? "<%s>" : "</%s>", name.toLowerCase()));
        }

        Tag(String name) {
            this(name, false);
        }

        public String getName() {
            return name;
        }

        public String getTagName() {
            return !isClosing() ? name : name.substring(1);
        }

        public boolean isClosing() {
            return name.startsWith("/");
        }

    }

    private Deque<String> tagStack = new ArrayDeque<>();
    private Deque<Slice> xmlQueue = new ArrayDeque<>();

    private String text;

    XmlCleaner(String text) {
        this.text = text;
    }

    private static boolean isTagValid(String name) {
        String tagName = !name.startsWith("/") ? name : name.substring(1);
        return MtextTags.isAllowed(tagName.toLowerCase());
    }

    private boolean startsWithTag(String name) {
        Slice head = xmlQueue.peekFirst();
        if (!(head instanceof Tag)) {
            return false;
        }
        return ((Tag) head).getName().equals(name);
    }

    private boolean endsWithTag(String name) {
        Slice tail = xmlQueue.peekLast();
        if (!(tail instanceof Tag)) {
            return false;
        }
        return ((Tag) tail).getName().equals(name);
    }

    private Tag buildTag(String tag) {
        Matcher m = TAG.matcher(tag);
        if (!m.matches()) {
            return null;
        }
        if (!isTagValid(m.group(1))) {
            return null;
        }
        StringBuilder out = new StringBuilder();
        out.append('<');
        out.append(m.group(1));
        String tagName = m.group(1).toLowerCase();
        String tail = m.group(2);
        while (tail.length() > 0) {
            m = ATTRIBUTE_QUOTED.matcher(tail);
            if (m.matches()) {
                out.append(' ');
                out.append(m.group(1));
                out.append('=');
                out.append(m.group(2));
                tail = m.group(3);
                continue;
            }
            m = ATTRIBUTE_SIMPLE.matcher(tail);
            if (m.matches()) {
                out.append(' ');
                out.append(m.group(1));
                out.append("=\"");
                out.append(m.group(2));
                out.append('"');
                tail = m.group(3);
                continue;
            }
            m = ATTRIBUTE_FLAG.matcher(tail);
            if (m.matches()) {
                out.append(' ');
                out.append(m.group(1));
                out.append("=\"");
                out.append(m.group(1));
                out.append('"');
                tail = m.group(2);
                continue;
            }
            tail = tail.substring(1);
        }
        return new Tag(tagName, out);
    }

    private boolean processTag(String tag) {
        Tag tagObject = buildTag(tag);
        if (tagObject == null) {
            return false;
        }
        String tagName = tagObject.getName();
        if (tagName.startsWith("/")) {
            if (!MtextTags.isEmpty(tagName.substring(1))) {
                tagObject.appendText(">");
                closeTag(tagObject);
            }
            // For empty tags closing tag is silently ignored
        } else {
            if (!MtextTags.isEmpty(tagName)) {
                tagObject.appendText(">");
                if (MtextTags.isChapter(tagName)) {
                    closeLowerLevelTags(tagObject);
                } else {
                    tagStack.push(tagName);
                    xmlQueue.addLast(tagObject);
                }
            } else {
                tagObject.appendText(" />");
                xmlQueue.addLast(tagObject);
            }
        }
        return true;
    }

    private void closeTag(Tag tagObject) {
        String tagName = tagObject != null ? tagObject.getTagName() : "";

        Deque<String> rstack = new ArrayDeque<>();
        while (!tagStack.isEmpty() && !tagStack.peek().equals(tagName)) {
            String tag = tagStack.pop();
            xmlQueue.addLast(new Tag(tag, true));
            rstack.push(tag);
        }
        if (StringUtils.isEmpty(tagName)) {
            return;
        }
        if (tagStack.isEmpty()) {
            xmlQueue.addLast(new Tag(tagName));
        } else {
            tagStack.pop();
        }
        xmlQueue.addLast(tagObject);
        while (!rstack.isEmpty()) {
            String tag = rstack.pop();
            xmlQueue.addLast(new Tag(tag));
            tagStack.push(tag);
        }
    }

    private void closeLowerLevelTags(Tag tagObject) {
        Deque<String> rstack = new ArrayDeque<>();
        while (!tagStack.isEmpty() && !MtextTags.isChapter(tagStack.peek())) {
            String tag = tagStack.pop();
            xmlQueue.addLast(new Tag(tag, true));
            rstack.push(tag);
        }
        xmlQueue.addLast(tagObject);
        tagStack.push(tagObject.getTagName());
        while (!rstack.isEmpty()) {
            String tag = rstack.pop();
            xmlQueue.addLast(new Tag(tag));
            tagStack.push(tag);
        }
    }

    private void crop() {
        while (!xmlQueue.isEmpty() && startsWithTag("br")) {
            xmlQueue.removeFirst();
        }
        while (!xmlQueue.isEmpty() && endsWithTag("br")) {
            xmlQueue.removeLast();
        }
    }

    private void deduplicate() {
        Deque<Slice> filtered = new ArrayDeque<>();
        for (Slice slice : xmlQueue) {
            if (!(slice instanceof Tag)) {
                filtered.addLast(slice);
                continue;
            }
            Tag tag = (Tag) slice;
            if (!(filtered.peekLast() instanceof Tag)) {
                filtered.addLast(tag);
                continue;
            }
            Tag prevTag = (Tag) filtered.peekLast();
            if (prevTag.getName().equals(tag.getName())
                    && !MtextTags.isEmpty(tag.getTagName())
                    && !tag.getTagName().equals("quote")) {
                continue;
            }
            if (prevTag.getName().equals("p") && tag.getTagName().equals("br")) {
                continue;
            }
            if (tag.getName().equals("/p")) {
                while (filtered.peekLast() instanceof Tag && ((Tag) filtered.peekLast()).getTagName().equals("br")) {
                    filtered.removeLast();
                }
                if (filtered.peekLast() instanceof Tag && ((Tag) filtered.peekLast()).getTagName().equals("p")) {
                    filtered.removeLast();
                    continue;
                }
            }
            filtered.addLast(tag);
        }
        xmlQueue = filtered;
    }

    private String joinQueue() {
        return xmlQueue.stream().map(Slice::toString).collect(Collectors.joining());
    }

    private String cleanup() {
        int i = 0;
        while (i < text.length()) {
            if (text.charAt(i) != '<') {
                int pos = text.indexOf('<', i);
                if (pos < 0) {
                    pos = text.length();
                }
                xmlQueue.addLast(new Slice(XmlUtils.delicateSpecialChars(text.substring(i, pos))));
                i = pos;
            } else {
                int pos = text.indexOf('>', i);
                if (pos < 0) {
                    xmlQueue.addLast(new Slice(XmlUtils.delicateSpecialChars(text.substring(i))));
                    i = text.length();
                    continue;
                }
                String tag = text.substring(i, pos + 1);
                if (!processTag(tag)) {
                    xmlQueue.addLast(new Slice("&lt;"));
                    i++;
                } else {
                    i = pos + 1;
                }
            }
        }
        closeTag(null);
        deduplicate();
        crop();
        return joinQueue();
    }

    public static String cleanup(String s) {
        return new XmlCleaner(s).cleanup();
    }

}