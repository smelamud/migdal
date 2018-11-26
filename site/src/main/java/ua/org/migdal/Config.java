package ua.org.migdal;

import javax.annotation.PostConstruct;

import org.springframework.boot.context.properties.ConfigurationProperties;

@ConfigurationProperties("migdal")
public class Config {

    private static Config instance;

    private String siteDomain;
    private String[] subdomains;
    private int sessionTimeoutShort;
    private int sessionTimeoutLong;
    private int publishingInterval;
    private boolean allowGuests;
    private String guestLogin;
    private boolean disableRegister;
    private int regConfirmTimeout;
    private String imageDir;
    private String imageUrl;
    private long maxImageSize;
    private int imageFileTimeout;
    private short innerImageMaxWidth;
    private short innerImageMaxHeight;
    private int innerImageTimeout;
    private int tinySize;
    private int tinySizeMinus;
    private int tinySizePlus;
    private int smallSize;
    private int smallSizeMinus;
    private int smallSizePlus;
    private int mediumSize;
    private int mediumSizeMinus;
    private int mediumSizePlus;
    private int inplaceSize;
    private int inplaceSizeMinus;
    private int inplaceSizePlus;
    private int topicFullNameEllipSize;
    private int mailSendLimit;
    private int mailSendPeriod;
    private String mailFromAddress;
    private String mailReplyToAddress;
    private String rootTopicUserName;
    private String rootTopicGroupName;
    private long rootTopicPerms;
    private long rootTopicModbits;
    private long defaultPostingPerms;
    private long defaultCommentPerms;
    private String captchaPublicKey;
    private String captchaSecretKey;
    private boolean htmlCache;

    @PostConstruct
    public void init() {
        instance = this;
    }

    public static Config getInstance() {
        return instance;
    }

    public String getSiteDomain() {
        return siteDomain;
    }

    public void setSiteDomain(String siteDomain) {
        this.siteDomain = siteDomain;
    }

    public String[] getSubdomains() {
        return subdomains;
    }

    public void setSubdomains(String[] subdomains) {
        this.subdomains = subdomains;
    }

    public int getSessionTimeoutShort() {
        return sessionTimeoutShort;
    }

    public void setSessionTimeoutShort(int sessionTimeoutShort) {
        this.sessionTimeoutShort = sessionTimeoutShort;
    }

    public int getSessionTimeoutLong() {
        return sessionTimeoutLong;
    }

    public void setSessionTimeoutLong(int sessionTimeoutLong) {
        this.sessionTimeoutLong = sessionTimeoutLong;
    }

    public int getPublishingInterval() {
        return publishingInterval;
    }

    public void setPublishingInterval(int publishingInterval) {
        this.publishingInterval = publishingInterval;
    }

    public boolean isAllowGuests() {
        return allowGuests;
    }

    public void setAllowGuests(boolean allowGuests) {
        this.allowGuests = allowGuests;
    }

    public String getGuestLogin() {
        return guestLogin;
    }

    public void setGuestLogin(String guestLogin) {
        this.guestLogin = guestLogin;
    }

    public boolean isDisableRegister() {
        return disableRegister;
    }

    public void setDisableRegister(boolean disableRegister) {
        this.disableRegister = disableRegister;
    }

    public int getRegConfirmTimeout() {
        return regConfirmTimeout;
    }

    public void setRegConfirmTimeout(int regConfirmTimeout) {
        this.regConfirmTimeout = regConfirmTimeout;
    }

    public String getImageDir() {
        return imageDir;
    }

    public void setImageDir(String imageDir) {
        this.imageDir = imageDir;
    }

    public String getImageUrl() {
        return imageUrl;
    }

    public void setImageUrl(String imageUrl) {
        this.imageUrl = imageUrl;
    }

    public long getMaxImageSize() {
        return maxImageSize;
    }

    public void setMaxImageSize(long maxImageSize) {
        this.maxImageSize = maxImageSize;
    }

    public int getImageFileTimeout() {
        return imageFileTimeout;
    }

    public void setImageFileTimeout(int imageFileTimeout) {
        this.imageFileTimeout = imageFileTimeout;
    }

    public short getInnerImageMaxWidth() {
        return innerImageMaxWidth;
    }

    public void setInnerImageMaxWidth(short innerImageMaxWidth) {
        this.innerImageMaxWidth = innerImageMaxWidth;
    }

    public short getInnerImageMaxHeight() {
        return innerImageMaxHeight;
    }

    public void setInnerImageMaxHeight(short innerImageMaxHeight) {
        this.innerImageMaxHeight = innerImageMaxHeight;
    }

    public int getInnerImageTimeout() {
        return innerImageTimeout;
    }

    public void setInnerImageTimeout(int innerImageTimeout) {
        this.innerImageTimeout = innerImageTimeout;
    }

    public int getTinySize() {
        return tinySize;
    }

    public void setTinySize(int tinySize) {
        this.tinySize = tinySize;
    }

    public int getTinySizeMinus() {
        return tinySizeMinus;
    }

    public void setTinySizeMinus(int tinySizeMinus) {
        this.tinySizeMinus = tinySizeMinus;
    }

    public int getTinySizePlus() {
        return tinySizePlus;
    }

    public void setTinySizePlus(int tinySizePlus) {
        this.tinySizePlus = tinySizePlus;
    }

    public int getSmallSize() {
        return smallSize;
    }

    public void setSmallSize(int smallSize) {
        this.smallSize = smallSize;
    }

    public int getSmallSizeMinus() {
        return smallSizeMinus;
    }

    public void setSmallSizeMinus(int smallSizeMinus) {
        this.smallSizeMinus = smallSizeMinus;
    }

    public int getSmallSizePlus() {
        return smallSizePlus;
    }

    public void setSmallSizePlus(int smallSizePlus) {
        this.smallSizePlus = smallSizePlus;
    }

    public int getMediumSize() {
        return mediumSize;
    }

    public void setMediumSize(int mediumSize) {
        this.mediumSize = mediumSize;
    }

    public int getMediumSizeMinus() {
        return mediumSizeMinus;
    }

    public void setMediumSizeMinus(int mediumSizeMinus) {
        this.mediumSizeMinus = mediumSizeMinus;
    }

    public int getMediumSizePlus() {
        return mediumSizePlus;
    }

    public void setMediumSizePlus(int mediumSizePlus) {
        this.mediumSizePlus = mediumSizePlus;
    }

    public int getInplaceSize() {
        return inplaceSize;
    }

    public void setInplaceSize(int inplaceSize) {
        this.inplaceSize = inplaceSize;
    }

    public int getInplaceSizeMinus() {
        return inplaceSizeMinus;
    }

    public void setInplaceSizeMinus(int inplaceSizeMinus) {
        this.inplaceSizeMinus = inplaceSizeMinus;
    }

    public int getInplaceSizePlus() {
        return inplaceSizePlus;
    }

    public void setInplaceSizePlus(int inplaceSizePlus) {
        this.inplaceSizePlus = inplaceSizePlus;
    }

    public int getTopicFullNameEllipSize() {
        return topicFullNameEllipSize;
    }

    public void setTopicFullNameEllipSize(int topicFullNameEllipSize) {
        this.topicFullNameEllipSize = topicFullNameEllipSize;
    }

    public int getMailSendLimit() {
        return mailSendLimit;
    }

    public void setMailSendLimit(int mailSendLimit) {
        this.mailSendLimit = mailSendLimit;
    }

    public int getMailSendPeriod() {
        return mailSendPeriod;
    }

    public void setMailSendPeriod(int mailSendPeriod) {
        this.mailSendPeriod = mailSendPeriod;
    }

    public String getMailFromAddress() {
        return mailFromAddress;
    }

    public void setMailFromAddress(String mailFromAddress) {
        this.mailFromAddress = mailFromAddress;
    }

    public String getMailReplyToAddress() {
        return mailReplyToAddress;
    }

    public void setMailReplyToAddress(String mailReplyToAddress) {
        this.mailReplyToAddress = mailReplyToAddress;
    }

    public String getRootTopicUserName() {
        return rootTopicUserName;
    }

    public void setRootTopicUserName(String rootTopicUserName) {
        this.rootTopicUserName = rootTopicUserName;
    }

    public String getRootTopicGroupName() {
        return rootTopicGroupName;
    }

    public void setRootTopicGroupName(String rootTopicGroupName) {
        this.rootTopicGroupName = rootTopicGroupName;
    }

    public long getRootTopicPerms() {
        return rootTopicPerms;
    }

    public void setRootTopicPerms(long rootTopicPerms) {
        this.rootTopicPerms = rootTopicPerms;
    }

    public long getRootTopicModbits() {
        return rootTopicModbits;
    }

    public void setRootTopicModbits(long rootTopicModbits) {
        this.rootTopicModbits = rootTopicModbits;
    }

    public long getDefaultPostingPerms() {
        return defaultPostingPerms;
    }

    public void setDefaultPostingPerms(long defaultPostingPerms) {
        this.defaultPostingPerms = defaultPostingPerms;
    }

    public long getDefaultCommentPerms() {
        return defaultCommentPerms;
    }

    public void setDefaultCommentPerms(long defaultCommentPerms) {
        this.defaultCommentPerms = defaultCommentPerms;
    }

    public String getCaptchaPublicKey() {
        return captchaPublicKey;
    }

    public void setCaptchaPublicKey(String captchaPublicKey) {
        this.captchaPublicKey = captchaPublicKey;
    }

    public String getCaptchaSecretKey() {
        return captchaSecretKey;
    }

    public void setCaptchaSecretKey(String captchaSecretKey) {
        this.captchaSecretKey = captchaSecretKey;
    }

    public boolean isHtmlCache() {
        return htmlCache;
    }

    public void setHtmlCache(boolean htmlCache) {
        this.htmlCache = htmlCache;
    }

}
