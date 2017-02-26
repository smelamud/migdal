package ua.org.migdal.mtext;

import java.io.IOException;
import java.io.StringReader;

import javax.xml.parsers.ParserConfigurationException;
import javax.xml.parsers.SAXParser;
import javax.xml.parsers.SAXParserFactory;

import org.xml.sax.ContentHandler;
import org.xml.sax.InputSource;
import org.xml.sax.SAXException;
import org.xml.sax.XMLReader;

import ua.org.migdal.mtext.exception.MtextConverterException;
import ua.org.migdal.util.XmlUtils;

public class MtextConverter {

    public static void convert(Mtext mtext, ContentHandler handler) throws MtextConverterException {
        try {
            SAXParserFactory saxParserFactory = SAXParserFactory.newInstance();
            SAXParser saxParser = saxParserFactory.newSAXParser();
            XMLReader xmlReader = saxParser.getXMLReader();
            xmlReader.setContentHandler(handler);
            xmlReader.parse(new InputSource(new StringReader(XmlUtils.delicateAmps(mtext.getXml(), false).toString())));
        } catch (ParserConfigurationException | SAXException | IOException e) {
            throw new MtextConverterException(e);
        }
    }

}
