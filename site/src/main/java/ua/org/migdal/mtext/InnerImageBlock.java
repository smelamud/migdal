package ua.org.migdal.mtext;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

import ua.org.migdal.data.ImagePlacement;
import ua.org.migdal.data.InnerImage;

public class InnerImageBlock {

    private int sizeX = 0;
    private int sizeY = 0;
    private short placement = ImagePlacement.CENTER;
    private List<InnerImage> images = new ArrayList<>();

    public int getSizeX() {
        return sizeX;
    }

    public void setSizeX(int sizeX) {
        this.sizeX = sizeX;
    }

    public int getSizeY() {
        return sizeY;
    }

    public void setSizeY(int sizeY) {
        this.sizeY = sizeY;
    }

    public short getPlacement() {
        return placement;
    }

    public void setPlacement(short placement) {
        this.placement = placement;
    }

    public boolean isPlaced(short place) {
        int hplace = place & ImagePlacement.HORIZONTAL;
        boolean h = hplace == 0 || (placement & ImagePlacement.HORIZONTAL) == hplace;
        int vplace = place & ImagePlacement.VERTICAL;
        boolean v = vplace == 0 || (placement & ImagePlacement.VERTICAL) == vplace;
        return h && v;
    }

    public void addImage(InnerImage image) {
        images.add(image);
    }

    public InnerImage getImage() {
        return images.size() > 0 ? images.get(0) : null;
    }

    public static Map<Integer, InnerImageBlock> getImageBlocks(List<InnerImage> images) {
        Map<Integer, InnerImageBlock> blocks = new HashMap<>();
        if (images == null) {
            return blocks;
        }
        for (InnerImage image : images) {
            int par = image.getParagraph();
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

}
