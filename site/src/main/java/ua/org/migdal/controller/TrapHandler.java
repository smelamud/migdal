package ua.org.migdal.controller;

import java.lang.reflect.Method;
import java.util.Arrays;

import org.springframework.util.StringUtils;
import org.springframework.web.util.UriComponents;
import org.springframework.web.util.UriComponentsBuilder;

import ua.org.migdal.data.Posting;
import ua.org.migdal.data.Topic;
import ua.org.migdal.data.User;
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

    public void setUserManager(UserManager userManager) {
        this.userManager = userManager;
    }

    public String getUri() {
        return builder.build(true).toUriString();
    }

    public static boolean fallsUnder(UriComponentsBuilder builder) {
        String path = builder.build().getPath();
        return path != null && path.contains(".php");
    }

    public String trap() {
        try {
            String methodName = buildTrapMethodName(builder.build().getPath());
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
            path("/links/");
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

    /*
    function trapLib_earview(array $args) {
        $id = trapInteger($args, 'image_id');
        $image = getImageById(getNewId('images', $id));
        if ($image->getId() <= 0)
            return '';
        return remakeMakeURI($image->getLargeImageURL(),
                $args,
                array('message_id', 'image_id'));
    }

    function trapLib_image(array $args) {
        $id = trapInteger($args, 'id');
        $size = trapString($args, 'size');
        if ($size != 'small' && $size != 'large')
            $size = 'small';
        $image = getImageById(getNewId('images', $id));
        if ($image->getId() <= 0)
            return '';
        return remakeMakeURI($size == 'small' ? $image->getSmallImageURL()
                        : $image->getLargeImageURL(),
                $args,
                array('id', 'size'));
    }

    function trapLinks(array $args) { // Must redirect to /internet/
        $id = trapInteger($args, 'topic_id');
        if ($id <= 0)
            return remakeMakeURI('/links/',
                    $args);
        else
            return remakeMakeURI('/'.catalogById(getNewId('topics', $id)),
                    $args,
                    array('topic_id'));
    }

    function trapMethodic_center(array $args) {
        return remakeMakeURI('/migdal/methodology/', $args);
    }

    function trapMethodics(array $args) {
        return remakeMakeURI('/migdal/methodology/books/', $args);
    }

    function trapMigdal(array $args) {
        $id = trapInteger($args, 'artid');
        if ($id <= 0)
            return remakeMakeURI('/migdal/', $args);
        $posting = getPostingById(getNewId('postings', $id));
        if ($posting->getId() > 0)
            return remakeMakeURI($posting->getGrpDetailsHref(),
                    $args,
                    array('artid'));
        else
            return '';
    }

    function trapMigdal_library_news(array $args) {
        return remakeMakeURI('/migdal/library/novelties/', $args);
    }

    function trapMigdal_library(array $args) {
        return remakeMakeURI('/migdal/library/', $args);
    }

    function trapMigdal_news(array $args) {
        $dirs = array(177 => 'museum',
                174 => 'mazltov',
                259 => 'beitenu');
        $id = trapInteger($args, 'topic_id');
        if ($id <= 0)
            return remakeMakeURI('/migdal/news/', $args, array('topic_id'));
        else
        if (isset($dirs[$id]))
            return remakeMakeURI("/migdal/{$dirs[$id]}/news/",
                    $args,
                    array('topic_id'));
        else
            return '';
    }

    function trapPosting(array $args) {
        $id = trapInteger($args, 'postid');
        $posting = getPostingById(getNewId('postings', $id));
        if ($posting->getId() > 0)
            return remakeMakeURI($posting->getGrpDetailsHref(),
                    $args,
                    array('postid'));
        else
            return '';
    }

    function trapPrintings(array $args) {
        return remakeMakeURI('/migdal/printings/', $args);
    }

    function trapRegister(array $args) {
        return remakeMakeURI('/register/', $args);
    }

    function trapSearch(array $args) {
        return remakeMakeURI('/search/', $args);
    }

    function trapTaglit($args) {
        return remakeMakeURI('/taglit/', $args);
    }

    function trapTaglit_user(array $args) {
        $user_id = trapInteger($args, 'user_id');
        $user = getUserById($user_id);
        if ($user->getId() <= 0)
            return '';
        return remakeMakeURI('/taglit/'.$user->getFolder().'/',
                $args,
                array('user_id'));
    }

    function trapThumbnail(array $args) {
        $id = trapInteger($args, 'id');
        $image = getImageById(getNewId('images', $id));
        if ($image->getId() <= 0)
            return '';
        return remakeMakeURI($image->getSmallImageURL(),
                $args,
                array('id', 'size'));
    }

    function trapTimes(array $args) {
        $issue = trapInteger($args, 'issue');
        if ($issue <= 0)
            return remakeMakeURI('/times/', $args);
        else
            return remakeMakeURI("/times/$issue/",
                    $args,
                    array('issue'),
                    array('issue' => $issue));
    }

    function trapTips(array $args) {
        return remakeMakeURI('/tips/', $args);
    }

    function trapUrls(array $args) {
        return remakeMakeURI('/links/urls/', $args, array('offset'));
    }

    function trapUserinfo(array $args) {
        $id = trapInteger($args, 'id');
        $user = getUserById($id);
        if ($user->getLogin() != '')
            return remakeMakeURI('/users/'.$user->getFolder().'/',
                $args,
                array('id'));
    else
        return '';
    }

    function trapUserinfo_panel(array $args) {
        return trapUserinfo($args);
    }

    function trapUserlost(array $args) {
        return remakeMakeURI('/remember-password/', $args);
    }

    function trapVeterans(array $args) {
        return remakeMakeURI('/veterans/', $args);
    }

    function trapVeterans_user(array $args) {
        $user_id = trapInteger($args, 'user_id');
        $user = getUserById($user_id);
        if ($user->getId() <= 0)
            return '';
        return remakeMakeURI('/veterans/'.$user->getFolder().'/',
                $args,
                array('user_id'));
    }

     */
}
