<?php
# @(#) $Id$

require_once('lib/old-ids.php');
require_once('lib/post.php');
require_once('lib/catalog.php');
require_once('lib/users.php');
require_once('lib/postings.php');

function trapInteger(array $args, $name) {
    return isset($args[$name]) ? postProcessInteger($args[$name]) : 0;
}

function trapString(array $args, $name) {
    return isset($args[$name]) ? postProcessString($args[$name]) : '';
}

function trapActions_userconfirm(array $args) {
    return remakeMakeURI('/actions/user/confirm/', $args);
}

function trapArchive(array $args) {
    return remakeMakeURI('/archive/', $args);
}

function trapArticle(array $args) {
    $id = trapInteger($args, 'artid');
    $posting = getPostingById(getNewId('postings', $id));
    if ($posting->getId() > 0)
        return remakeMakeURI($posting->getGrpDetailsHref(),
                             $args,
                             array('artid'));
    else
        return '';
}

function trapArticle_times(array $args) {
    $id = trapInteger($args, 'artid');
    $posting = getPostingById(getNewId('postings', $id));
    if ($posting->getId() > 0)
        return remakeMakeURI($posting->getGrpDetailsHref(),
                             $args,
                             array('artid'));
    else
        return '';
}

function trapBook_chapter(array $args) {
    $id = trapInteger($args, 'chapid');
    $posting = getPostingById(getNewId('postings', $id));
    if ($posting->getId() > 0)
        return remakeMakeURI($posting->getGrpDetailsHref(),
                             $args,
                             array('chapid'));
    else
        return '';
}

function trapBook_chapter_split(array $args) {
    $id = trapInteger($args, 'chapid');
    $posting = getPostingById(getNewId('postings', $id));
    if ($posting->getId() > 0)
        return remakeMakeURI($posting->getGrpDetailsHref(),
                             $args,
                             array('chapid'));
    else
        return '';
}

function trapBook(array $args) {
    $id = trapInteger($args, 'bookid');
    $posting = getPostingById(getNewId('postings', $id));
    if ($posting->getId() > 0)
        return remakeMakeURI($posting->getGrpDetailsHref(),
                             $args,
                             array('bookid'));
    else
        return '';
}

function trapBook_print(array $args) {
    $id = trapInteger($args, 'bookid');
    $posting = getPostingById(getNewId('postings', $id));
    if ($posting->getId() > 0)
        return remakeMakeURI($posting->getGrpDetailsHref().'print/',
                             $args,
                             array('bookid'));
    else
        return '';
}

function trapBook_split(array $args) {
    $id = trapInteger($args, 'bookid');
    $posting = getPostingById(getNewId('postings', $id));
    if ($posting->getId() > 0)
        return remakeMakeURI($posting->getGrpDetailsHref(),
                             $args,
                             array('bookid'));
    else
        return '';
}

function trapBook_static(array $args) {
    $id = trapInteger($args, 'bookid');
    $posting = getPostingById(getNewId('postings', $id));
    if ($posting->getId() > 0)
        return remakeMakeURI($posting->getGrpDetailsHref(),
                             $args,
                             array('bookid'));
    else
        return '';
}

function trapChat(array $args) {
    return remakeMakeURI('/chat-archive/', $args);
}

function trapChatboard(array $args) {
    return remakeMakeURI('/chat-archive/', $args);
}

function trapChatconsole(array $args) {
    return remakeMakeURI('/chat-archive/', $args);
}

function trapEvent(array $args) {
    $id = trapInteger($args, 'topic_id');
    $topic = getTopicById(getNewId('topics', $id));
    if ($topic->getId() > 0)
        return remakeMakeURI('/'.$topic->getCatalog(),
                             $args,
                             array('topic_id'));
    else
        return '';
}

function trapEvents_english(array $args) {
    return remakeMakeURI('/events/', $args);
}

function trapEvents(array $args) {
    return remakeMakeURI('/migdal/events/', $args);
}

function trapForumcatalog(array $args) {
    return remakeMakeURI('/forum/', $args, array('topic_id'));
}

function trapForum(array $args) {
    $id = trapInteger($args, 'msgid');
    $id = getNewId('postings', $id);
    if (postingExists($id))
        return remakeMakeURI("/forum/$id/",
                             $args,
                             array('msgid'));
    else
        return '';
}

function trapGallery(array $args) {
    $topic_id = trapInteger($args, 'topic_id');
    $user_id = trapInteger($args, 'user_id');
    $general = trapInteger($args, 'general');
    $rm = array('topic_id', 'user_id', 'general', 'grp');
    if ($topic_id == 143) /* Галерея музея */
        $topic_id = 175;
    if ($user_id <= 0)
        if ($topic_id <= 0)
            return remakeMakeURI('/gallery/', $args, $rm);
        else {
            $topic = getTopicById(getNewId('topics', $topic_id));
            if ($topic->getId() > 0)
                if ($general <= 0)
                    return remakeMakeURI('/gallery/'.$topic->getCatalog(),
                                         $args,
                                         $rm);
                else
                    return remakeMakeURI('/'.$topic->getCatalog().'gallery/',
                                         $args,
                                         $rm);
            else
                return '';
        }
    else {
        $topic = getTopicById(getNewId('topics', $topic_id));
        if ($topic->getId() <= 0)
            return '';
        $user = getUserById($user_id);
        if ($user->getId() <= 0)
            return '';
        return remakeMakeURI('/gallery/'.$topic->getCatalog().$user->getFolder().'/',
                             $args,
                             $rm);
    }
}

function trapHalom_main(array $args) {
    return remakeMakeURI('/migdal/events/', $args);
}

function trapHalom(array $args) {
    $postid = trapInteger($args, 'postid');
    $topic_id = trapInteger($args, 'topic_id');
    $day = trapInteger($args, 'day');
    $rm = array('postid', 'topic_id', 'day');
    if ($day <= 0)
        $day = 1;
    if($postid > 0) {
        $posting = getPostingById(getNewId('postings', $postid));
        if ($posting->getId() > 0)
            return remakeMakeURI($posting->getGrpDetailsHref(),
                                 $args,
                                 $rm);
        else
            return '';
    } else {
        $topic = getTopicById(getNewId('topics', $topic_id));
        if ($topic->getId() > 0)
            return remakeMakeURI('/'.$topic->getCatalog()."day-$day/",
                                 $args,
                                 $rm);
        else
            return '';
    }
}

function trapHelp(array $args) {
    $id = trapInteger($args, 'artid');
    $posting = getPostingById(getNewId('postings', $id));
    if ($posting->getId() > 0)
        return remakeMakeURI($posting->getGrpDetailsHref(),
                             $args,
                             array('artid'));
    else
        return '';
}

function trapIndex(array $args) {
    $id = trapInteger($args, 'topic_id');
    if ($id == 5) /* Еврейский Интернет */
        return remakeMakeURI('/links/',
                             $args,
                             array('topic_id'));
    if ($id == 13) /* КЕС */
        return remakeMakeURI('/migdal/jcc/student/',
                             $args,
                             array('topic_id'));
    if ($id == 24) /* Ту би-Шват */
        return remakeMakeURI('/judaism/',
                             $args,
                             array('topic_id'));
    if ($id == 146) /* Методический центр */
        return remakeMakeURI('/migdal/methodology/books/',
                             $args,
                             array('topic_id'));
    if ($id <= 0)
        return remakeMakeURI('/', $args);
    else
        return remakeMakeURI('/'.catalogById(getNewId('topics', $id)),
                             $args,
                             array('topic_id'));
}

function trapJcc(array $args) {
    return remakeMakeURI('/migdal/jcc/', $args);
}

function trapKaitana_main(array $args) {
    return remakeMakeURI('/migdal/events/', $args);
}

function trapKaitana(array $args) {
    $postid = trapInteger($args, 'postid');
    $topic_id = trapInteger($args, 'topic_id');
    $day = trapInteger($args, 'day');
    $rm = array('postid', 'topic_id', 'day');
    if ($day <= 0)
        $day = 1;
    if ($postid > 0) {
        $posting = getPostingById(getNewId('postings', $postid));
        if ($posting->getId() > 0)
            return remakeMakeURI($posting->getGrpDetailsHref(),
                                 $args,
                                 $rm);
        else
            return '';
    } else {
        if ($topic_id <= 0)
            return remakeMakeURI("/migdal/events/kaitanot/5762/summer/day-$day/",
                                 $args,
                                 $rm);;
        $topic = getTopicById(getNewId('topics', $topic_id));
        if ($topic->getId() > 0)
            return remakeMakeURI('/'.$topic->getCatalog()."day-$day/",
                                 $args,
                                 $rm);
        else
            return '';
    }
}

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

function trapLinks(array $args) {
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
?>
