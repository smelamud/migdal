package ua.org.migdal.mtext;

import java.util.Stack;

import org.unbescape.html.HtmlEscape;
import org.xml.sax.Attributes;
import org.xml.sax.SAXException;
import org.xml.sax.SAXParseException;
import org.xml.sax.helpers.DefaultHandler;
import ua.org.migdal.util.XmlUtils;

class MtextToLength extends DefaultHandler {

    private int len;
    private boolean clearTags;

    private StringBuilder shortened = new StringBuilder();
    private Stack<String> open = new Stack<>();
    private int clen = 0;
    private boolean stop = false;

    MtextToLength(int len) {
        this(len, false);
    }

    MtextToLength(int len, boolean clearTags) {
        this.len = len;
        this.clearTags = clearTags;
    }

    public CharSequence getShortened() {
        return shortened;
    }

    @Override
    public void error(SAXParseException e) throws SAXException {
        shortened.append("<b>");
        shortened.append(e.getMessage());
        shortened.append("</b>");
    }

    @Override
    public void startElement(String uri, String localName, String qName, Attributes attributes) throws SAXException {
        if (stop) {
            return;
        }
        
        switch (qName) {
            case "mtext-line":
            case "mtext-short":
            case "mtext-long":
                break;

            case "email":
            case "br":
                if (!clearTags) {
                    shortened.append(XmlUtils.makeTag(qName, attributes, true));
                }
                break;

            default:
                if (!clearTags) {
                    shortened.append(XmlUtils.makeTag(qName, attributes));
                }
                open.push(qName);
        }
    }

    @Override
    public void endElement(String uri, String localName, String qName) throws SAXException {
        switch (qName) {
            case "mtext-line":
            case "mtext-short":
            case "mtext-long":
                if (!clearTags) {
                    while (!open.empty()) {
                        shortened.append(XmlUtils.makeTag("/" + open.pop()));
                    }
                }
                return;
        }

        if (stop) {
            return;
        }

        switch (qName) {
            case "email":
            case "br":
                break;

            case "p":
            case "li":
            case "center":
            case "quote":
            case "h2":
            case "h3":
            case "h4":
                clen++;
                /* fall through */
            default:
                if (!clearTags) {
                    shortened.append(XmlUtils.makeTag("/" + qName));
                }
                open.pop();
        }
        if (clen >= len) {
            stop = true;
        }
    }

    @Override
    public void characters(char[] ch, int start, int length) throws SAXException {
        if (stop) {
            return;
        }

        String data = new String(ch, start, length);
        int n = HtmlEscape.unescapeHtml(data).length();
        String text = clen + n < len ? data : data.substring(0, n - (clen - len));
        if (!clearTags) {
            text = XmlUtils.delicateSpecialChars(text);
        }
        shortened.append(text);
        clen += n;
        if (clen >= len) {
            stop = true;
        }
    }

}
