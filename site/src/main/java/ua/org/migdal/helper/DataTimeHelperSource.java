package ua.org.migdal.helper;

@HelperSource
public class DataTimeHelperSource {

    public CharSequence now() {
        return Long.toString(System.currentTimeMillis());
    }

}
