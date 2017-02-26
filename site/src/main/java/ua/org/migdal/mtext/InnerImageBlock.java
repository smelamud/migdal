package ua.org.migdal.mtext;

import java.util.ArrayList;
import java.util.List;

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

}
