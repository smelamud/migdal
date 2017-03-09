package ua.org.migdal.mtext;

import org.xml.sax.ContentHandler;

import ua.org.migdal.mtext.exception.MtextConverterException;
import ua.org.migdal.util.XmlConverter;
import ua.org.migdal.util.exception.XmlConverterException;

public class MtextConverter {

    public static void convert(Mtext mtext, ContentHandler handler) throws MtextConverterException {
        convert(mtext.getXml(), handler);
    }

    public static void convert(String xml, ContentHandler handler) throws MtextConverterException {
        try {
            XmlConverter.convert(xml, handler);
        } catch (XmlConverterException e) {
            throw new MtextConverterException(e);
        }
    }

}
