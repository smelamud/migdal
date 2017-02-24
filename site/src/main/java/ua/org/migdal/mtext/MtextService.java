package ua.org.migdal.mtext;

import java.io.IOException;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

import javax.xml.parsers.ParserConfigurationException;
import javax.xml.parsers.SAXParser;
import javax.xml.parsers.SAXParserFactory;

import org.springframework.stereotype.Service;

import org.xml.sax.SAXException;
import org.xml.sax.XMLReader;
import ua.org.migdal.data.InnerImage;

@Service
public class MtextService {

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

    public MtextHtml mtextToHtml(String body, MtextFormat format, long id, List<InnerImage> innerImages) {
        try {
            SAXParserFactory saxParserFactory = SAXParserFactory.newInstance();
            SAXParser saxParser = saxParserFactory.newSAXParser();
            XMLReader xmlReader = saxParser.getXMLReader();
            MtextToHtml handler = new MtextToHtml(format, id, getImageBlocks(innerImages));
            xmlReader.setContentHandler(handler);
            xmlReader.parse(body);
            return new MtextHtml(handler.getHtmlBody(), handler.getHtmlFootnotes());
        } catch (ParserConfigurationException | SAXException | IOException e) {
            return new MtextHtml(String.format("<b>** Exception: %s</b>", e.getMessage()));
        }
    }

}