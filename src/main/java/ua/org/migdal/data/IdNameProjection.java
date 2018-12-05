package ua.org.migdal.data;

import ua.org.migdal.Config;

public class IdNameProjection {

    private long id;
    private String name;

    public IdNameProjection(long id, String name) {
        this.id = id;
        this.name = name;
    }

    public long getId() {
        return id;
    }

    public void setId(long id) {
        this.id = id;
    }

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }

    public String getNameShort() {
        int maxSize = Config.getInstance().getTopicFullNameEllipSize();
        return name.length() <= maxSize ? name : "..." + name.substring(name.length() - maxSize + 3);
    }

}