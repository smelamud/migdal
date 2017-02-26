package ua.org.migdal.helper;

import java.util.HashMap;
import java.util.List;
import java.util.Map;

import com.github.jknack.handlebars.Handlebars;

import com.github.jknack.handlebars.Options;
import ua.org.migdal.data.InnerImage;
import ua.org.migdal.mtext.InnerImageBlock;
import ua.org.migdal.mtext.Mtext;
import ua.org.migdal.mtext.MtextConverter;
import ua.org.migdal.mtext.MtextToHtml;
import ua.org.migdal.mtext.exception.MtextConverterException;

@HelperSource
public class MtextHelperSource {

    private static Map<Integer, InnerImageBlock> getImageBlocks(List<InnerImage> images) {
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

    public CharSequence mtext(Mtext mtext, Options options) {
        boolean ignoreWrongFormat = HelperUtils.boolArg(options.hash("ignoreWrongFormat", false));
        List<InnerImage> innerImages = options.hash("innerImages");

        MtextToHtml handler =
                new MtextToHtml(mtext.getFormat(), ignoreWrongFormat, mtext.getId(), getImageBlocks(innerImages));
        try {
            MtextConverter.convert(mtext, handler);
            return new Handlebars.SafeString(handler.getHtmlBody());
        } catch (MtextConverterException e) {
            return new Handlebars.SafeString(
                    String.format("<b>** %s: %s</b>", e.getMessage(), e.getCause().getMessage()));
        }
    }

}
