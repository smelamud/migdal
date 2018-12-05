package ua.org.migdal.util;

import java.security.NoSuchAlgorithmException;

import ua.org.migdal.data.User;

public class Password {

    public static void assign(User user, String password) throws NoSuchAlgorithmException {
        user.setPassword(Utils.md5(password));
    }

    public static boolean validate(User user, String password) throws NoSuchAlgorithmException {
        String md5Password = Utils.md5(password);
        return user != null && md5Password.equalsIgnoreCase(user.getPassword());
    }

}