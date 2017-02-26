package ua.org.migdal.mtext;

import java.io.IOException;
import java.io.StringReader;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

import javax.xml.parsers.ParserConfigurationException;
import javax.xml.parsers.SAXParser;
import javax.xml.parsers.SAXParserFactory;

import org.springframework.stereotype.Service;

import org.xml.sax.InputSource;
import org.xml.sax.SAXException;
import org.xml.sax.XMLReader;
import ua.org.migdal.data.InnerImage;
import ua.org.migdal.util.XmlUtils;

@Service
public class MtextService {

    private ThreadLocal<ImageCallback> imageCallback = new ThreadLocal<>();
    private ThreadLocal<UserNameCallback> userNameCallback = new ThreadLocal<>();
    private ThreadLocal<IncutCallback> incutCallback = new ThreadLocal<>();

    public ImageCallback getImageCallback() {
        return imageCallback.get();
    }

    public void setImageCallback(ImageCallback imageCallback) {
        this.imageCallback.set(imageCallback);
    }

    public UserNameCallback getUserNameCallback() {
        return userNameCallback.get();
    }

    public void setUserNameCallback(UserNameCallback userNameCallback) {
        this.userNameCallback.set(userNameCallback);
    }

    public IncutCallback getIncutCallback() {
        return incutCallback.get();
    }

    public void setIncutCallback(IncutCallback incutCallback) {
        this.incutCallback.set(incutCallback);
    }

    private Map<Integer, InnerImageBlock> getImageBlocks(List<InnerImage> images) {
        Map<Integer, InnerImageBlock> blocks = new HashMap<>();
        if (images == null) {
            return blocks;
        }
        for (InnerImage image : images) {
            int par = image.getPar();
            if (!blocks.containsKey(par)) {
                blocks.put(par, new InnerImageBlock());
            }
            InnerImageBlock block = blocks.get(par);
            block.addImage(image);
            block.setSizeX(Math.max(block.getSizeX(), image.getX() + 1));
            block.setSizeY(Math.max(block.getSizeY(), image.getY() + 1));
            block.setPlacement(image.getPlacement());
        }
        return blocks;
    }

    public MtextHtml mtextToHtml(Mtext mtext, boolean ignoreWrongFormat, List<InnerImage> innerImages) {
        return mtextToHtml(mtext.getXml(), mtext.getFormat(), ignoreWrongFormat, mtext.getId(), innerImages);
    }

    public MtextHtml mtextToHtml(String body, MtextFormat format, boolean ignoreWrongFormat, long id,
                                 List<InnerImage> innerImages) {
        try {
            SAXParserFactory saxParserFactory = SAXParserFactory.newInstance();
            SAXParser saxParser = saxParserFactory.newSAXParser();
            XMLReader xmlReader = saxParser.getXMLReader();
            MtextToHtml handler = new MtextToHtml(format, ignoreWrongFormat, id, getImageBlocks(innerImages));
            xmlReader.setContentHandler(handler);
            handler.setImageCallback(getImageCallback());
            handler.setUserNameCallback(getUserNameCallback());
            handler.setIncutCallback(getIncutCallback());
            xmlReader.parse(new InputSource(new StringReader(XmlUtils.delicateAmps(body, false).toString())));
            return new MtextHtml(handler.getHtmlBody(), handler.getHtmlFootnotes());
        } catch (ParserConfigurationException | SAXException | IOException e) {
            return new MtextHtml(String.format("<b>** XML error: %s</b>", e.getMessage()));
        }
    }

}