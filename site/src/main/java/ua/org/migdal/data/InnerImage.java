package ua.org.migdal.data;

// FIXME temporary stub
public interface InnerImage {

    int getPar();
    short getX();
    short getY();
    short getPlacement();
    boolean isPlaced(short place);
    Image getImage();

}