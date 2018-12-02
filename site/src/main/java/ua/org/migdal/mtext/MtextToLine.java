package ua.org.migdal.mtext;

import org.unbescape.html.HtmlEscape;
import org.xml.sax.SAXException;
import org.xml.sax.SAXParseException;
import org.xml.sax.helpers.DefaultHandler;

class MtextToLine extends DefaultHandler {

    private StringBuilder line = new StringBuilder();

    public CharSequence getLine() {
        return line;
    }

    @Override
    public void error(SAXParseException e) throws SAXException {
        line.append(e.getMessage());
    }

    @Override
    public void endElement(String uri, String localName, String qName) throws SAXException {
        switch (qName.toLowerCase()) {
            case "p":
            case "li":
            case "center":
            case "quote":
            case "h2":
            case "h3":
            case "h4":
                line.append('\u001F');
        }
    }

    @Override
    public void characters(char[] ch, int start, int length) throws SAXException {
        line.append(HtmlEscape.unescapeHtml(new String(ch, start, length)));
    }

}