package ua.org.migdal.manager;

import java.sql.Timestamp;

import javax.inject.Inject;

import org.springframework.stereotype.Service;

import ua.org.migdal.Config;
import ua.org.migdal.data.ContentVersion;
import ua.org.migdal.data.ContentVersionRepository;
import ua.org.migdal.data.EntryType;
import ua.org.migdal.data.HtmlCache;
import ua.org.migdal.data.HtmlCacheRepository;
import ua.org.migdal.util.Utils;

@Service
public class HtmlCacheManager {

    private final Object contentVersionLock = new Object();

    private ContentVersion contentVersion;

    @Inject
    private Config config;

    @Inject
    private ContentVersionRepository contentVersionRepository;

    @Inject
    private HtmlCacheRepository htmlCacheRepository;

    @Inject
    private EntryManager entryManager;

    private void fetchContentVersion() {
        if (contentVersion == null) {
            synchronized (contentVersionLock) {
                contentVersion = contentVersionRepository.findById(1L).orElse(new ContentVersion());
            }
        }
    }

    public void postingsUpdated() {
        fetchContentVersion();
        synchronized (contentVersionLock) {
            contentVersion.setPostingsVersion(contentVersion.getPostingsVersion() + 1);
            contentVersionRepository.save(contentVersion);
        }
    }

    public void commentsUpdated() {
        fetchContentVersion();
        synchronized (contentVersionLock) {
            contentVersion.setCommentsVersion(contentVersion.getCommentsVersion() + 1);
            contentVersionRepository.save(contentVersion);
        }
    }

    public void topicsUpdated() {
        fetchContentVersion();
        synchronized (contentVersionLock) {
            contentVersion.setTopicsVersion(contentVersion.getTopicsVersion() + 1);
            contentVersionRepository.save(contentVersion);
        }
    }

    public void entryChanged(EntryType entryType) {
        switch (entryType) {
            case POSTING:
                postingsUpdated();
                break;
            case COMMENT:
                commentsUpdated();
                break;
            case TOPIC:
                topicsUpdated();
                break;
            default:
                /* do nothing */
                break;
        }
    }

    public CachedHtml of(String objectName) {
        return new CachedHtml(this, objectName);
    }

    public boolean isValid(CachedHtml cachedHtml) {
        return get(cachedHtml) != null;
    }

    public String get(CachedHtml cachedHtml) {
        fetchContentVersion();
        HtmlCache htmlCache = htmlCacheRepository.findById(cachedHtml.getIdent()).orElse(null);
        if (htmlCache == null) {
            return null;
        }
        if (cachedHtml.getPeriod() != null
                && (htmlCache.getDeadline() == null || htmlCache.getDeadline().before(Utils.now()))) {
            return null;
        }
        if (cachedHtml.isDependsOnPostings()
                && (htmlCache.getPostingsVersion() == null
                    || htmlCache.getPostingsVersion() != contentVersion.getPostingsVersion())) {
            return null;
        }
        if (cachedHtml.isDependsOnComments()
                && (htmlCache.getCommentsVersion() == null
                || htmlCache.getCommentsVersion() != contentVersion.getCommentsVersion())) {
            return null;
        }
        if (cachedHtml.isDependsOnTopics()
                && (htmlCache.getTopicsVersion() == null
                || htmlCache.getTopicsVersion() != contentVersion.getTopicsVersion())) {
            return null;
        }
        return htmlCache.getContent();
    }

    public void store(CachedHtml cachedHtml, CharSequence content) {
        if (!cachedHtml.isEnabled() || !config.isHtmlCache()) {
            return;
        }

        HtmlCache htmlCache = new HtmlCache();
        htmlCache.setIdent(cachedHtml.getIdent());
        htmlCache.setContent(content.toString().replaceAll("\\s+", " "));
        if (cachedHtml.getPeriod() != null) {
            htmlCache.setDeadline(Timestamp.from(Utils.now().toInstant().plus(cachedHtml.getPeriod())));
        }
        if (cachedHtml.isDependsOnPostings()) {
            htmlCache.setPostingsVersion(contentVersion.getPostingsVersion());
        }
        if (cachedHtml.isDependsOnComments()) {
            htmlCache.setCommentsVersion(contentVersion.getCommentsVersion());
        }
        if (cachedHtml.isDependsOnTopics()) {
            htmlCache.setTopicsVersion(contentVersion.getTopicsVersion());
        }
        htmlCacheRepository.save(htmlCache);
    }

}
