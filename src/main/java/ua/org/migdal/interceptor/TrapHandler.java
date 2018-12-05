package ua.org.migdal.interceptor;

import java.lang.reflect.Method;
import java.util.Arrays;

import org.springframework.util.StringUtils;
import org.springframework.web.util.UriComponents;
import org.springframework.web.util.UriComponentsBuilder;

import ua.org.migdal.data.Entry;
import ua.org.migdal.data.Posting;
import ua.org.migdal.data.Topic;
import ua.org.migdal.data.User;
import ua.org.migdal.manager.EntryManager;
import ua.org.migdal.manager.OldIdManager;
import ua.org.migdal.manager.PostingManager;
import ua.org.migdal.manager.TopicManager;
import ua.org.migdal.manager.UserManager;

public class TrapHandler {

    private static class TrapError extends RuntimeException {
    }

    private UriComponentsBuilder builder;
    private UriComponents components;

    private OldIdManager oldIdManager;
    private TopicManager topicManager;
    private PostingManager postingManager;
    private EntryManager entryManager;
    private UserManager userManager;

    public TrapHandler(UriComponentsBuilder builder) {
        this.builder = builder;
        components = builder.build();
    }

    public void setOldIdManager(OldIdManager oldIdManager) {
        this.oldIdManager = oldIdManager;
    }

    public void setTopicManager(TopicManager topicManager) {
        this.topicManager = topicManager;
    }

    public void setPostingManager(PostingManager postingManager) {
        this.postingManager = postingManager;
    }

    public void setEntryManager(EntryManager entryManager) {
        this.entryManager = entryManager;
    }

    public void setUserManager(UserManager userManager) {
        this.userManager = userManager;
    }

    public String getUri() {
        return builder.build(true).toUriString();
    }

    public static boolean fallsUnder(UriComponentsBuilder builder) {
        String path = builder.build().getPath();
        return path != null
                && (path.contains(".php")
                    || path.equals("/") && builder.build().getQueryParams().containsKey("topic_id"));
    }

    public String trap() {
        try {
            String methodName = path().equals("/") ? "trapIndex" : buildTrapMethodName(path());
            Method method = TrapHandler.class.getDeclaredMethod(methodName);
            if (method == null) {
                trapError();
            }
            method.invoke(this);
        } catch (Exception e) {
            return null;
        }
        return getUri();
    }

    private static String buildTrapMethodName(String path) {
        if (path == null) {
            trapError();
        }
        StringBuilder buf = new StringBuilder("trap");
        int pos = path.indexOf(".php");
        if (pos <= 1) {
            trapError();
        }
        Arrays.stream(path.substring(1, pos).split("[/-]")).map(StringUtils::capitalize).forEach(buf::append);
        return buf.toString();
    }

    private static void trapError() {
        throw new TrapError();
    }

    private String path() {
        return components.getPath();
    }

    private void path(String path) {
        builder.replacePath(path);
    }

    private String param(String name) {
        String value = components.getQueryParams().getFirst(name);
        if (value == null) {
            trapError();
        }
        return value;
    }

    private String param(String name, String defaultValue) {
        String value = components.getQueryParams().getFirst(name);
        return value != null ? value : defaultValue;
    }

    private long intParam(String name) {
        try {
            return Long.parseLong(param(name));
        } catch (NumberFormatException e) {
            trapError();
        }
        return 0; // unreacheable indeed
    }

    private long intParam(String name, long defaultValue) {
        try {
            return Long.parseLong(param(name, Long.toString(defaultValue)));
        } catch (NumberFormatException e) {
            trapError();
        }
        return 0; // unreacheable indeed
    }

    private void delParam(String... names) {
        for (String name : names) {
            builder.replaceQueryParam(name);
        }
    }

    private long newId(String tableName, long oldId) {
        Long entryId = oldIdManager.getEntryId(tableName, oldId);
        if (entryId == null) {
            trapError();
        }
        return entryId;
    }

    private Posting getPosting(long id) {
        Posting posting = postingManager.get(id);
        if (posting == null) {
            trapError();
        }
        return posting;
    }

    private Topic getTopic(long id) {
        Topic topic = topicManager.get(id);
        if (topic == null) {
            trapError();
        }
        return topic;
    }

    private Entry getEntry(long id) {
        Entry entry = entryManager.get(id);
        if (entry == null) {
            trapError();
        }
        return entry;
    }

    private User getUser(long id) {
        User user = userManager.get(id);
        if (user == null) {
            trapError();
        }
        return user;
    }

    /* Trap methods */

    private void trapActionsUserconfirm() {
        path("/actions/user/confirm/");
    }

    private void trapArchive() {
        path("/archive/");
    }

    private void trapArticle() {
        path(getPosting(newId("postings", intParam("artid"))).getGrpDetailsHref());
        delParam("artid");
    }

    private void trapArticleTimes() {
        path(getPosting(newId("postings", intParam("artid"))).getGrpDetailsHref());
        delParam("artid");
    }

    private void trapBookChapter() {
        path(getPosting(newId("postings", intParam("chapid"))).getGrpDetailsHref());
        delParam("chapid");
    }

    private void trapBookChapterSplit() {
        path(getPosting(newId("postings", intParam("chapid"))).getGrpDetailsHref());
        delParam("chapid");
    }

    private void trapBook() {
        path(getPosting(newId("postings", intParam("bookid"))).getGrpDetailsHref());
        delParam("bookid");
    }

    private void trapBookPrint() {
        path(getPosting(newId("postings", intParam("bookid"))).getGrpDetailsHref() + "print/");
        delParam("bookid");
    }

    private void trapBookSplit() {
        path(getPosting(newId("postings", intParam("bookid"))).getGrpDetailsHref());
        delParam("bookid");
    }

    private void trapBookStatic() {
        path(getPosting(newId("postings", intParam("bookid"))).getGrpDetailsHref());
        delParam("bookid");
    }

    private void trapChat() {
        path("/chat-archive/");
    }

    private void trapChatboard() {
        path("/chat-archive/");
    }

    private void trapChatconsole() {
        path("/chat-archive/");
    }

    private void trapEvent() {
        path(getTopic(newId("topics", intParam("topic_id"))).getHref());
        delParam("topic_id");
    }

    private void trapEventsEnglish() {
        path("/events/");
    }

    private void trapEvents() {
        path("/migdal/events/");
    }

    private void trapForumcatalog() {
        path("/forum/");
        delParam("topic_id");
    }

    private void trapForum() {
        path(String.format("/forum/%d/", getPosting(newId("postings", intParam("msgid"))).getId()));
        delParam("msgid");
    }

    private void trapGallery() {
        long topicId = intParam("topic_id", 0);
        long userId = intParam("user_id", 0);
        delParam("topic_id", "user_id", "general", "grp");
        if (topicId == 143) { /* Галерея музея */
            topicId = 175;
        }
        if (userId <= 0) {
            if (topicId <= 0) {
                path("/");
            } else {
                path(getTopic(newId("topics", topicId)).getHref() + "gallery/");
            }
        } else {
            Topic topic = getTopic(newId("topics", topicId));
            User user = getUser(userId);
            path(topic.getHref() + user.getFolder() + "/");
        }
    }

    private void trapHalomMain() {
        path("/migdal/events/");
    }

    private void trapHalom() {
        long postingId = intParam("postid", 0);
        long topicId = intParam("topic_id", 0);
        long day = intParam("day", 1);
        delParam("postid", "topic_id", "day");
        if (day <= 0) {
            day = 1;
        }
        if (postingId > 0) {
            path(getPosting(newId("postings", postingId)).getGrpDetailsHref());
        } else {
            path(String.format("%sday-%d/", getTopic(newId("topics", topicId)).getHref(), day));
        }
    }

    private void trapHelp() {
        path(getPosting(newId("postings", intParam("artid"))).getGrpDetailsHref());
        delParam("artid");
    }

    private void trapIndex() {
        long id = intParam("topic_id", 0);
        delParam("topic_id");
        if (id == 5) { /* Еврейский Интернет */
            path("/internet/");
        } else if (id == 13) { /* КЕС */
            path("/migdal/jcc/student/");
        } else if (id == 24) { /* Ту би-Шват */
            path("/judaism/");
        } else if (id == 146) { /* Методический центр */
            path("/migdal/methodology/books/");
        } else if (id <= 0) {
            path("/");
        } else {
            path(getTopic(newId("topics", id)).getHref());
        }
    }

    private void trapJcc() {
        path("/migdal/jcc/");
    }

    private void trapKaitanaMain() {
        path("/migdal/events/");
    }

    private void trapKaitana() {
        long postingId = intParam("postid", 0);
        long topicId = intParam("topic_id", 0);
        long day = intParam("day", 1);
        delParam("postid", "topic_id", "day");
        if (day <= 0) {
            day = 1;
        }
        if (postingId > 0) {
            path(getPosting(newId("postings", postingId)).getGrpDetailsHref());
        } else {
            if (topicId <= 0) {
                path(String.format("/migdal/events/kaitanot/5762/summer/day-%d/", day));
            } else {
                path(String.format("%sday-%d/", getTopic(newId("topics", topicId)).getHref(), day));
            }
        }
    }

    private void trapLibEarview() {
        path(getPosting(newId("images", intParam("image_id"))).getLargeImageUrl());
        delParam("message_id", "image_id");
    }

    private void trapLibImage() {
        long id = intParam("id");
        String size = param("size", "small");
        delParam("id", "size");
        Entry entry = getEntry(newId("images", id));
        if (size.equals("large")) {
            path(entry.getLargeImageUrl());
        } else {
            path(entry.getSmallImageUrl());
        }
    }

    private void trapLinks() {
        path("/internet/");
    }

    private void trapMethodicCenter() {
        path("/migdal/methodology/");
    }

    private void trapMethodics() {
        path("/migdal/methodology/books/");
    }

    private void trapMigdal() {
        long id = intParam("artid", 0);
        delParam("artid");
        if (id <= 0) {
            path("/migdal/");
        } else {
            path(getPosting(newId("postings", id)).getGrpDetailsHref());
        }
    }

    private void trapMigdalLibraryNews() {
        path("/migdal/library/novelties/");
    }

    private void trapMigdalLibrary() {
        path("/migdal/library/");
    }

    private void trapMigdalNews() {
        long id = intParam("topic_id", 0);
        delParam("topic_id");
        if (id <= 0) {
            path("/migdal/news/");
        } else if (id == 177) {
            path("/migdal/museum/news/");
        } else if (id == 174) {
            path("/migdal/mazltov/news/");
        } else if (id == 259) {
            path("/migdal/beitenu/news/");
        } else {
            trapError();
        }
    }

    private void trapPosting() {
        path(getPosting(newId("postings", intParam("postid"))).getGrpDetailsHref());
        delParam("postid");
    }

    private void trapPrintings() {
        path("/migdal/printings/");
    }

    private void trapRegister() {
        path("/register/");
    }

    private void trapSearch() {
        path("/search/");
    }

    private void trapTaglit() {
        path("/taglit/");
    }

    private void trapTaglitUser() {
        path(String.format("/taglit/%s/", getUser(intParam("user_id")).getFolder()));
        delParam("user_id");
    }

    private void trapThumbnail() {
        path(getEntry(newId("images", intParam("id"))).getSmallImageUrl());
        delParam("id", "size");
    }

    private void trapTimes() {
        long issue = intParam("issue", 0);
        delParam("issue");
        if (issue <= 0) {
            path("/times/");
        } else {
            path(String.format("/times/%d/", issue));
        }
    }

    private void trapTips() {
        path("/tips/");
    }

    private void trapUrls() {
        path("/internet/");
        delParam("offset");
    }

    private void trapUserinfo() {
        path(String.format("/user/%s/", getUser(intParam("id")).getFolder()));
        delParam("id");
    }

    private void trapUserinfoPanel() {
        trapUserinfo();
    }

    private void trapUserlost() {
        path("/remember-password/");
    }

    private void trapVeterans() {
        path("/veterans/");
    }

    private void trapVeteransUser() {
        path(String.format("/veterans/%s/", getUser(intParam("user_id")).getFolder()));
        delParam("user_id");
    }

}
