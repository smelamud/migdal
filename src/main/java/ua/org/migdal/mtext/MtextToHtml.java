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

import ua.org.migdal.Config;
import ua.org.migdal.data.ImagePlacement;
import ua.org.migdal.data.InnerImage;
import ua.org.migdal.helper.ArticleHelperSource;
import ua.org.migdal.helper.UsersHelperSource;
import ua.org.migdal.util.XmlUtils;

public class MtextToHtml extends DefaultHandler implements MtextConverted {

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
    private Stack<Character> listStyles = new Stack<>();
    private int noteNo = 1;
    private Map<Integer, InnerImageBlock> imageBlocks;
    private int par = 0;
    private boolean inHx = false;
    private boolean brInHx = false;

    private Config config;
    private ArticleHelperSource articleHelperSource;
    private UsersHelperSource usersHelperSource;
    private IncutCallback incutCallback;

    public MtextToHtml(MtextFormat format, boolean ignoreWrongFormat, long id,
                       Map<Integer, InnerImageBlock> imageBlocks) {
        this.format = format;
        this.ignoreWrongFormat = ignoreWrongFormat;
        this.id = id;
        this.imageBlocks = imageBlocks;

        html = htmlBody;
    }

    @Override
    public CharSequence getHtmlBody() {
        return htmlBody;
    }

    @Override
    public CharSequence getHtmlFootnotes() {
        return htmlFootnotes;
    }

    public Config getConfig() {
        return config;
    }

    public void setConfig(Config config) {
        this.config = config;
    }

    public ArticleHelperSource getArticleHelperSource() {
        return articleHelperSource;
    }

    public void setArticleHelperSource(ArticleHelperSource articleHelperSource) {
        this.articleHelperSource = articleHelperSource;
    }

    public UsersHelperSource getUsersHelperSource() {
        return usersHelperSource;
    }

    public void setUsersHelperSource(UsersHelperSource usersHelperSource) {
        this.usersHelperSource = usersHelperSource;
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
        html.append(String.format("<b>%s</b>", e.getMessage()));
    }

    private String getInplaceFootnote() {
        String footnote = new Mtext(xmlFootnote.toString(), format, id)
                            .shortenNote(
                                    config.getInplaceSize(),
                                    config.getInplaceSizeMinus(),
                                    config.getInplaceSizePlus());
        return footnote.replace('\n', ' ');
    }

    private String getParagraphClear() {
        if (format.lessThan(MtextFormat.LONG)) {
            return "none";
        }
        InnerImageBlock block = imageBlocks.get(par);
        if (block == null) {
            return "none";
        }
        if (block.isPlaced(ImagePlacement.CENTERLEFT)) {
            return "left";
        }
        if (block.isPlaced(ImagePlacement.CENTERRIGHT)) {
            return "right";
        }
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
            html.append(articleHelperSource.articleImage(id, par, image, align));
        } else {
            html.append(articleHelperSource.articleImage(id, par, null, ""));
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

    private String verifyUrl(String url) {
        if (url.toLowerCase().contains("javascript:")) {
            return "";
        }
        return url;
    }

    @Override
    public void startElement(String uri, String localName, String qName, Attributes attributes) throws SAXException {
        String qiName = qName.toLowerCase();
        if (!MtextTags.isAllowed(qiName) || MtextTags.getLevel(qiName).greaterThan(format)) {
            if (!ignoreWrongFormat) {
                html.append("<b>** &lt;");
                html.append(qiName);
                html.append("&gt; **</b>");
            } else {
                html.append(' ');
            }
            return;
        }
        if (inFootnote) {
            xmlFootnote.append(XmlUtils.makeTag(qiName, attributes));
        }
        switch (qiName) {
            case "mtext-line":
            case "mtext-short":
            case "mtext-long":
                break;

            case "a": {
                if (attributes.getIndex("href") < 0) {
                    html.append("<b>&lt;A HREF?&gt;</b>");
                    break;
                }
                String href = verifyUrl(attributes.getValue("href"));
                // Attribute "LOCAL" may be present, but ignored for now
                html.append(XmlUtils.makeTag(qiName, Collections.singletonMap("href", href)));
                break;
            }

            case "email":
                if (attributes.getIndex("addr") < 0) {
                    html.append("<b>&lt;EMAIL ADDR?&gt;</b>");
                    break;
                }
                html.append(XmlUtils.makeTag("a",
                        Collections.singletonMap("href", "mailto:" + attributes.getValue("addr"))));
                html.append(XmlUtils.makeText(attributes.getValue("addr")));
                html.append(XmlUtils.makeTag("/a"));
                break;

            case "user":
                if (attributes.getIndex("name") < 0 && attributes.getIndex("guest-name") < 0) {
                    html.append("<b>&lt;USER NAME?&gt;</b>");
                    break;
                }
                if (attributes.getIndex("name") >= 0) {
                    html.append(usersHelperSource.userNameLink(attributes.getValue("name"), null));
                } else {
                    html.append(usersHelperSource.userNameLink(null, attributes.getValue("guest-name")));
                }
                break;

            case "h2":
            case "h3":
            case "h4":
                html.append(XmlUtils.makeTag(qiName, attributes));
                inHx = true;
                brInHx = false;
                break;

            case "br":
                html.append(XmlUtils.makeTag(qiName, attributes, true));
                if (inHx && !brInHx) {
                    brInHx = true;
                    html.append(XmlUtils.makeTag("span", Collections.singletonMap("class", "subheading")));
                }
                break;

            case "p": {
                putImageBlock();
                String clear = attributes.getIndex("clear") >= 0 ? attributes.getValue("clear") : getParagraphClear();
                if (clear.equals("none")) {
                    html.append(XmlUtils.makeTag(qiName));
                } else {
                    html.append(XmlUtils.makeTag(qiName, Collections.singletonMap("style", "clear: " + clear)));
                }
                break;
            }

            case "li":
                if (listStyles.empty()) {
                    html.append("<b>&lt;LI?&gt;</b>");
                    break;
                }
                if (listStyles.peek() != 'd') {
                    html.append(XmlUtils.makeTag(qiName));
                }
                if (attributes.getIndex("title") >= 0) {
                    if (listStyles.peek() != 'd') {
                        html.append(XmlUtils.makeTag("i"));
                        html.append(XmlUtils.makeText(attributes.getValue("title")));
                        html.append(XmlUtils.makeTag("/i"));
                        html.append(' ');
                    } else {
                        html.append(XmlUtils.makeTag("dt"));
                        html.append(XmlUtils.makeTag("i"));
                        html.append(XmlUtils.makeText(attributes.getValue("title")));
                        html.append(XmlUtils.makeTag("/i"));
                        html.append(XmlUtils.makeTag("/dt"));
                    }
                }
                if (listStyles.peek() == 'd') {
                    html.append(XmlUtils.makeTag("dd"));
                }
                break;

            case "ul":
                listStyles.push('u');
                listStyles.push('i');
                html.append(XmlUtils.makeTag(qiName, attributes));
                break;

            case "ol":
                listStyles.push('o');
                listStyles.push('i');
                html.append(XmlUtils.makeTag(qiName, attributes));
                break;

            case "dl": {
                listStyles.push('d');
                String font = attributes.getValue("font");
                listStyles.push(StringUtils.isEmpty(font) ? 'b' : font.charAt(0));
                html.append(XmlUtils.makeTag(qiName, attributes));
                break;
            }

            case "quote":
                html.append(XmlUtils.makeTag("div", Collections.singletonMap("class", "quote")));
                break;

            case "footnote":
                htmlFootnote = new StringBuilder();
                xmlFootnote = new StringBuilder();
                html = htmlFootnote;
                inFootnote = true;
                html.append(XmlUtils.makeTag("a", Collections.singletonMap("href", "#_ref" + noteNo)));
                if (attributes.getIndex("title") < 0) {
                    footnoteTitle = "";
                    html.append(XmlUtils.makeTag("sup"));
                    html.append(noteNo);
                    html.append(XmlUtils.makeTag("/sup"));
                    html.append(XmlUtils.makeTag("/a"));
                } else {
                    footnoteTitle = attributes.getValue("title");
                    html.append(footnoteTitle);
                    html.append(XmlUtils.makeTag("/a"));
                    html.append(" &mdash; ");
                }
                html.append(XmlUtils.makeTag("a", Collections.singletonMap("name", "_note" + noteNo)));
                html.append(XmlUtils.makeTag("/a"));
                break;

            case "incut": {
                String align = attributes.getValue("align");
                String width = attributes.getValue("width");
                html.append(invokeIncutOpenCallback(
                        align != null ? align : "right",
                        width != null ? width : "50%"));
                break;
            }

            default:
                html.append(XmlUtils.makeTag(qiName, attributes));
        }
    }

    @Override
    public void endElement(String uri, String localName, String qName) throws SAXException {
        String qiName = qName.toLowerCase();
        if (!MtextTags.isAllowed(qiName) || MtextTags.getLevel(qiName).greaterThan(format)) {
            if (!ignoreWrongFormat) {
                html.append("<b>** &lt;/");
                html.append(qiName);
                html.append("&gt; **</b>");
            } else {
                html.append(' ');
            }
            return;
        }
        switch (qiName) {
            case "mtext-line":
            case "mtext-short":
            case "mtext-long":
            case "br":
                break;

            case "h2":
            case "h3":
            case "h4":
                if (brInHx) {
                    html.append(XmlUtils.makeTag("/span"));
                }
                inHx = false;
                brInHx = false;
                html.append(XmlUtils.makeTag("/" + qiName));
                break;

            case "li":
                if (listStyles.empty()) {
                    html.append("<b>&lt;/LI?&gt;</b>");
                    break;
                }
                if (listStyles.peek() != 'd') {
                    html.append(XmlUtils.makeTag("/" + qiName));
                } else {
                    html.append(XmlUtils.makeTag("/dd"));
                }
                break;

            case "ul":
            case "ol":
            case "dl":
                if (listStyles.empty()) {
                    html.append("<b>&lt;/");
                    html.append(qiName.toUpperCase());
                    html.append("?&gt;</b>");
                    break;
                }
                listStyles.pop();
                html.append(XmlUtils.makeTag("/" + qiName));
                break;

            case "quote":
                html.append(XmlUtils.makeTag("/div"));
                break;

            case "footnote":
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

            case "incut":
                html.append(invokeIncutCloseCallback());
                break;

            default:
                html.append(XmlUtils.makeTag("/" + qiName));
        }
        if (inFootnote) {
            xmlFootnote.append(XmlUtils.makeTag("/" + qiName));
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