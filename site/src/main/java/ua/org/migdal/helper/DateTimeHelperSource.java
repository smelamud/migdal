package ua.org.migdal.helper;

@HelperSource
public class DateTimeHelperSource {

    public CharSequence now() {
        return Long.toString(System.currentTimeMillis());
    }

}
