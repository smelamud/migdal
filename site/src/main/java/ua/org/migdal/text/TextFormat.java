package ua.org.migdal.text;

public enum TextFormat {

    PLAIN("Простой текст (без переносов строк)", true),
    TEX("Простой текст (с переносами строк)", false),
    XML("XML", true),
    MAIL("Текст с цитированием", false);

    private String title;
    private boolean user;

    TextFormat(String title, boolean user) {
        this.title = title;
        this.user = user;
    }

    public String getTitle() {
        return title;
    }

    public int getValue() {
        return ordinal();
    }

    public boolean isUser() {
        return user;
    }

}