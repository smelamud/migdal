package ua.org.migdal.mtext;

public interface UserNameCallback {

    CharSequence format(boolean guest, String login);

}