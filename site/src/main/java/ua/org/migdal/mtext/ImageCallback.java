package ua.org.migdal.mtext;

import ua.org.migdal.data.Image;

public interface ImageCallback {

    CharSequence format(long id, int par, Image image, String align);

}