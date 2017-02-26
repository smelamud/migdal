package ua.org.migdal.mtext;

import java.util.Collections;
import java.util.HashMap;
import java.util.Map;
import java.util.Stack;

import org.springframework.util.StringUtils;
import org.xml.sax.Attributes;
import org.xml.sax.SAXException;
import org.xml.sax.SAXParseException;
import org.xml.sax.helpers.DefaultHandler;

import ua.org.migdal.data.Image;
import ua.org.migdal.data.ImagePlacement;
import ua.org.migdal.data.InnerImage;
import ua.org.migdal.util.XmlUtils;

class MtextToHtml extends DefaultHandler {

    /**
     * Points to {@link #htmlBody} or {@link #htmlFootnote}
     */
    private StringBuilder html;
    private StringBuilder htmlBody = new StringBuilder();
    private boolean inFootnote = false;
    private String footnoteTitle;
    private StringBuilder htmlFootnote;
    private StringBuilder xmlFootnote;
    private StringBuilder htmlFootnotes = new StringBuilder();
    private MtextFormat format;
    private boolean ignoreWrongFormat;
    private long id;
    private Stack<Character> listFonts = new Stack<>();
    private Stack<Character> listStyles = new Stack<>();
    private int noteNo = 1;
    private Map<Integer, InnerImageBlock> imageBlocks;
    private int par = 0;
    private boolean inHx = false;
    private boolean brInHx = false;

    private ImageCallback imageCallback;
    private UserNameCallback userNameCallback;
    private IncutCallback incutCallback;

    public MtextToHtml(MtextFormat format, boolean ignoreWrongFormat, long id,
                       Map<Integer, InnerImageBlock> imageBlocks) {
        this.format = format;
        this.ignoreWrongFormat = ignoreWrongFormat;
        this.id = id;
        this.imageBlocks = imageBlocks;

        html = htmlBody;
        listFonts.push('i');
    }

    public CharSequence getHtmlBody() {
        return htmlBody;
    }

    public CharSequence getHtmlFootnotes() {
        return htmlFootnotes;
    }

    public ImageCallback getImageCallback() {
        return imageCallback;
    }

    public void setImageCallback(ImageCallback imageCallback) {
        this.imageCallback = imageCallback;
    }

    private CharSequence invokeImageCallback(long id, int par, Image image, String align) {
        return imageCallback != null ? imageCallback.format(id, par, image, align) : "";
    }

    public UserNameCallback getUserNameCallback() {
        return userNameCallback;
    }

    public void setUserNameCallback(UserNameCallback userNameCallback) {
        this.userNameCallback = userNameCallback;
    }

    private CharSequence invokeUserNameCallback(boolean guest, String login) {
        return userNameCallback != null ? userNameCallback.format(guest, login) : "";
    }

    public IncutCallback getIncutCallback() {
        return incutCallback;
    }

    public void setIncutCallback(IncutCallback incutCallback) {
        this.incutCallback = incutCallback;
    }

    private CharSequence invokeIncutOpenCallback(String align, String width) {
        return incutCallback != null ? incutCallback.formatOpen(align, width) : "";
    }

    private CharSequence invokeIncutCloseCallback() {
        return incutCallback != null ? incutCallback.formatClose() : "";
    }

    @Override
    public void error(SAXParseException e) throws SAXException {
        html.append("<b>$message</b>");
    }

    private String getInplaceFootnote() {
        /*global $inplaceSize, $inplaceSizeMinus, $inplaceSizePlus;

        return strtr(shortenNote($this->xmlFootnote, $inplaceSize,
                $inplaceSizeMinus, $inplaceSizePlus),
                "\n", ' ');*/
        return xmlFootnote.toString();
    }

    private String getParagraphClear() {
        if (format.lessThan(MtextFormat.LONG)) {
            return "none";
        }
        InnerImageBlock block = imageBlocks.get(par);
        if (block == null)
            return "none";
        if (block.isPlaced(ImagePlacement.CENTERLEFT))
            return "left";
        if (block.isPlaced(ImagePlacement.CENTERRIGHT))
            return "right";
        return "none";
    }

    private void putImage(InnerImage image, int par) {
        if (image != null) {
            String align = "";
            if (image.isPlaced(ImagePlacement.LEFT)) {
                align = "left";
            }
            if (image.isPlaced(ImagePlacement.HCENTER)) {
                align = "center";
            }
            if (image.isPlaced(ImagePlacement.RIGHT)) {
                align = "right";
            }
            html.append(invokeImageCallback(id, par, image.getImage(), align));
        } else {
            html.append(invokeImageCallback(id, par, null, ""));
        }
    }

    private void putImageBlock() {
        if (format.lessThan(MtextFormat.LONG)) {
            return;
        }

        int par = this.par++;
        InnerImageBlock block = imageBlocks.get(par);
        InnerImage image = block != null ? block.getImage() : null;
        putImage(image, par);
    }

    private String verifyURL(String url) {
        if (url.toLowerCase().contains("javascript:"))
            return "";
        return url;
    }

    @Override
    public void startElement(String uri, String localName, String qName, Attributes attributes) throws SAXException {
        String name = qName.toUpperCase();
        if (!MtextTagLevel.TAGS.containsKey(name) || MtextTagLevel.TAGS.get(name).greaterThan(format)) {
            if (!ignoreWrongFormat) {
                html.append("<b>** &lt;");
                html.append(name);
                html.append("&gt; **</b>");
            } else {
                html.append(' ');
            }
            return;
        }
        if (inFootnote) {
            xmlFootnote.append(XmlUtils.makeTag(name, attributes));
        }
        switch (name) {
            case "MTEXT-LINE":
            case "MTEXT-SHORT":
            case "MTEXT-LONG":
                break;

            case "A":
                if (attributes.getIndex("HREF") < 0) {
                    html.append("<b>&lt;A HREF?&gt;</b>");
                    break;
                }
                String href = verifyURL(attributes.getValue("HREF"));
                // Attribute "LOCAL" may be present, but ignored for now
                html.append(XmlUtils.makeTag(name, Collections.singletonMap("href", href)));
                break;

            case "EMAIL":
                if (attributes.getIndex("ADDR") < 0) {
                    html.append("<b>&lt;EMAIL ADDR?&gt;</b>");
                    break;
                }
                html.append(XmlUtils.makeTag("a",
                        Collections.singletonMap("href", "mailto:" + attributes.getValue("ADDR"))));
                html.append(XmlUtils.makeText(attributes.getValue("ADDR")));
                html.append(XmlUtils.makeTag("/a"));
                break;

            case "USER":
                if (attributes.getIndex("NAME") < 0 && attributes.getIndex("GUEST-NAME") < 0) {
                    html.append("<b>&lt;USER NAME?&gt;</b>");
                    break;
                }
                if (attributes.getIndex("NAME") >= 0) {
                    html.append(invokeUserNameCallback(false, attributes.getValue("NAME")));
                } else {
                    html.append(invokeUserNameCallback(true, attributes.getValue("GUEST-NAME")));
                }
                break;

            case "H2":
            case "H3":
            case "H4":
                html.append(XmlUtils.makeTag(name, attributes));
                inHx = true;
                brInHx = false;
                break;

            case "BR":
                html.append(XmlUtils.makeTag(name, attributes, true));
                if (inHx && !brInHx) {
                    brInHx = true;
                    html.append(XmlUtils.makeTag("span", Collections.singletonMap("class", "subheading")));
                }
                break;

            case "P": {
                putImageBlock();
                String clear = attributes.getIndex("CLEAR") >= 0 ? attributes.getValue("CLEAR") : getParagraphClear();
                if (clear.equals("none")) {
                    html.append(XmlUtils.makeTag(name));
                } else {
                    html.append(XmlUtils.makeTag(name, Collections.singletonMap("style", "clear: " + clear)));
                }
                break;
            }

            case "LI":
                if (listStyles.empty()) {
                    html.append("<b>&lt;LI?&gt;</b>");
                    break;
                }
                if (listStyles.peek() != 'd') {
                    html.append(XmlUtils.makeTag(name));
                }
                if (attributes.getIndex("TITLE") >= 0) {
                    if (listStyles.peek() != 'd') {
                        html.append(XmlUtils.makeTag(listFonts.peek().toString()));
                        html.append(XmlUtils.makeText(attributes.getValue("TITLE")));
                        html.append(XmlUtils.makeTag("/" + listFonts.peek()));
                        html.append(' ');
                    } else {
                        html.append(XmlUtils.makeTag("dt"));
                        html.append(XmlUtils.makeTag(listFonts.peek().toString()));
                        html.append(XmlUtils.makeText(attributes.getValue("TITLE")));
                        html.append(XmlUtils.makeTag("/" + listFonts.peek()));
                        html.append(XmlUtils.makeTag("/dt"));
                    }
                }
                if (listStyles.peek() == 'd') {
                    html.append(XmlUtils.makeTag("dd"));
                }
                break;

            case "UL":
                listStyles.push('u');
                listStyles.push('i');
                html.append(XmlUtils.makeTag(name, attributes));
                break;

            case "OL":
                listStyles.push('o');
                listStyles.push('i');
                html.append(XmlUtils.makeTag(name, attributes));
                break;

            case "DL": {
                listStyles.push('d');
                String font = attributes.getValue("FONT");
                listStyles.push(StringUtils.isEmpty(font) ? 'b' : font.charAt(0));
                html.append(XmlUtils.makeTag(name, attributes));
                break;
            }

            case "QUOTE":
                html.append(XmlUtils.makeTag("div", Collections.singletonMap("class", "quote")));
                break;

            case "FOOTNOTE":
                htmlFootnote = new StringBuilder();
                xmlFootnote = new StringBuilder();
                html = htmlFootnote;
                inFootnote = true;
                html.append(XmlUtils.makeTag("a", Collections.singletonMap("href", "#_ref" + noteNo)));
                if (attributes.getIndex("TITLE") < 0) {
                    footnoteTitle = "";
                    html.append(XmlUtils.makeTag("sup"));
                    html.append(noteNo);
                    html.append(XmlUtils.makeTag("/sup"));
                    html.append(XmlUtils.makeTag("/a"));
                } else {
                    footnoteTitle = attributes.getValue("TITLE");
                    html.append(footnoteTitle);
                    html.append(XmlUtils.makeTag("/a"));
                    html.append(" &mdash; ");
                }
                html.append(XmlUtils.makeTag("a", Collections.singletonMap("name", "_note" + noteNo)));
                html.append(XmlUtils.makeTag("/a"));
                break;

            case "INCUT": {
                String align = attributes.getValue("ALIGN");
                String width = attributes.getValue("WIDTH");
                html.append(invokeIncutOpenCallback(
                        align != null ? align : "right",
                        width != null ? width : "50%"));
                break;
            }

            default:
                html.append(XmlUtils.makeTag(name, attributes));
        }
    }

    @Override
    public void endElement(String uri, String localName, String qName) throws SAXException {
        String name = qName.toUpperCase();
        if (!MtextTagLevel.TAGS.containsKey(name) || MtextTagLevel.TAGS.get(name).greaterThan(format)) {
            if (!ignoreWrongFormat) {
                html.append("<b>** &lt;/");
                html.append(name);
                html.append("&gt; **</b>");
            } else {
                html.append(' ');
            }
            return;
        }
        switch (name) {
            case "MTEXT-LINE":
            case "MTEXT-SHORT":
            case "MTEXT-LONG":
            case "BR":
                break;

            case "H2":
            case "H3":
            case "H4":
                if (brInHx) {
                    html.append(XmlUtils.makeTag("/span"));
                }
                inHx = false;
                brInHx = false;
                html.append(XmlUtils.makeTag("/" + name));
                break;

            case "LI":
                if (listStyles.empty()) {
                    html.append("<b>&lt;/LI?&gt;</b>");
                    break;
                }
                if (listStyles.peek() != 'd') {
                    html.append(XmlUtils.makeTag("/" + name));
                } else {
                    html.append(XmlUtils.makeTag("/dd"));
                }
                break;

            case "UL":
            case "OL":
            case "DL":
                listStyles.pop();
                listFonts.pop();
                html.append(XmlUtils.makeTag("/" + name));
                break;

            case "QUOTE":
                html.append(XmlUtils.makeTag("/div"));
                break;

            case "FOOTNOTE":
                html.append(XmlUtils.makeTag("br", (Attributes) null, true));
                htmlFootnotes.append(htmlFootnote);
                html = htmlBody;
                inFootnote = false;
                html.append(XmlUtils.makeTag("a", Collections.singletonMap("name", "_ref" + noteNo)));
                html.append(XmlUtils.makeTag("/a"));
                if (StringUtils.isEmpty(footnoteTitle)) {
                    html.append(XmlUtils.makeTag("sup"));
                    Map<String, String> attrs = new HashMap<>();
                    attrs.put("href", "#_note" + noteNo);
                    attrs.put("title", getInplaceFootnote());
                    html.append(XmlUtils.makeTag("a", attrs));
                    html.append(noteNo);
                    html.append(XmlUtils.makeTag("/a"));
                    html.append(XmlUtils.makeTag("/sup"));
                } else {
                    Map<String, String> attrs = new HashMap<>();
                    attrs.put("href", "#_note" + noteNo);
                    attrs.put("title", getInplaceFootnote());
                    html.append(XmlUtils.makeTag("a", attrs));
                    html.append(XmlUtils.makeText(footnoteTitle));
                    html.append(XmlUtils.makeTag("/a"));
                }
                noteNo++;
                break;

            case "INCUT":
                html.append(invokeIncutCloseCallback());
                break;

            default:
                html.append(XmlUtils.makeTag("/" + name));
        }
        if (inFootnote) {
            xmlFootnote.append(XmlUtils.makeTag("/" + name));
        }
    }

    @Override
    public void characters(char[] ch, int start, int length) throws SAXException {
        String s = XmlUtils.makeText(new String(ch, start, length));
        html.append(s);
        if (inFootnote) {
            xmlFootnote.append(s);
        }
    }

}