package ua.org.migdal;

import org.springframework.boot.context.properties.ConfigurationProperties;

@ConfigurationProperties("migdal")
public class Config {

    private String siteDomain;

    public String getSiteDomain() {
        return siteDomain;
    }

    public void setSiteDomain(String siteDomain) {
        this.siteDomain = siteDomain;
    }

}
