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

    private static final Pattern TAG = Pattern.compile("<\\s*(\\/?\\w+)\\s*(.*)>");
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
            this(name, String.format(!isClosing ? "<%s>" : "</%s>", name.toLowerCase()));
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
        if (xmlQueue.isEmpty()) {
            return false;
        }
        Slice head = xmlQueue.getFirst();
        if (!(head instanceof Tag)) {
            return false;
        }
        return ((Tag) head).getTagName().equals(name);
    }

    private boolean endsWithTag(String name) {
        if (xmlQueue.isEmpty()) {
            return false;
        }
        Slice tail = xmlQueue.getLast();
        if (!(tail instanceof Tag)) {
            return false;
        }
        return ((Tag) tail).getTagName().equals(name);
    }

    private boolean processTag(String tag) {
        Matcher m = TAG.matcher(tag);
        if (!m.matches()) {
            return false;
        }
        if (!isTagValid(m.group(1))) {
            return false;
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
        Tag tagObject = new Tag(tagName, out);
        if (tagName.startsWith("/")) {
            if (!MtextTags.isEmpty(tagName.substring(1))) {
                tagObject.appendText(">");
                closeTag(tagName.substring(1), tagObject);
            }
            // For empty tags closing tag is silently ignored
        } else {
            if (!MtextTags.isEmpty(tagName)) {
                tagObject.appendText(">");
                tagStack.push(tagName);
                xmlQueue.addLast(tagObject);
            } else {
                tagObject.appendText(" />");
                xmlQueue.addLast(tagObject);
            }
        }
        return true;
    }

    private void closeTag(String tagName, Tag tagObject) {
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

    private String joinQueue() {
        while (!xmlQueue.isEmpty()) {
            if (startsWithTag("br")) {
                xmlQueue.removeFirst();
                continue;
            }
            if (xmlQueue.size() >= 2 && startsWithTag("p")) {
                Slice head = xmlQueue.removeFirst();
                if (startsWithTag("/p")) {
                    xmlQueue.removeFirst();
                    continue;
                }
                xmlQueue.addFirst(head);
            }
            break;
        }
        while (!xmlQueue.isEmpty()) {
            if (endsWithTag("br")) {
                xmlQueue.removeLast();
                continue;
            }
            if (xmlQueue.size() >= 2 && endsWithTag("/p")) {
                Slice tail = xmlQueue.removeLast();
                if (endsWithTag("p")) {
                    xmlQueue.removeLast();
                    continue;
                }
                xmlQueue.addLast(tail);
            }
            break;
        }
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
        closeTag("", null);
        return joinQueue();
    }

    public static String cleanup(String s) {
        return new XmlCleaner(s).cleanup();
    }

}