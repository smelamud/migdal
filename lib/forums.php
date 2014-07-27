<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/limitselect.php');
require_once('lib/entries.php');
require_once('lib/permissions.php');
require_once('lib/utils.php');
require_once('lib/bug.php');
require_once('lib/answers.php');
require_once('lib/sql.php');
require_once('lib/image-files.php');
require_once('lib/text-any.php');
require_once('lib/catalog.php');
require_once('lib/html-cache.php');

require_once('conf/forums.php');

class Forum
        extends Entry {

    protected $parent_type;

    public function __construct(array $row = array()) {
        global $tfForum;

        $this->entry = ENT_FORUM;
        $this->body_format = $tfForum;
        parent::__construct($row);
    }

    public function setup($vars) {
        global $tfForum, $tfLarge;

        if (!isset($vars['edittag']) || !$vars['edittag'])
            return;
        $this->body_format = $tfForum;
        $this->body = $vars['body'];
        $this->body_xml = anyToXML($this->body, $this->body_format,
                                   MTEXT_SHORT);
        $this->large_body_format = $vars['large_body_format'];
        if (!c_digit($this->large_body_format)
            || $this->large_body_format > TF_MAX)
            $this->large_body_format = $tfLarge;
        $this->has_large_body = 0;
        $this->large_body = '';
        $this->large_body_xml = '';
        if ($vars['large_body'] != '') {
            $this->has_large_body = 1;
            $this->large_body = $vars['large_body'];
            $this->large_body_xml = anyToXML($this->large_body,
                                             $this->large_body_format,
                                             MTEXT_LONG);
        }
        if ($vars['large_body_filename'] != '')
            $this->large_body_filename = $vars['large_body_filename'];
        $this->small_image = $vars['small_image'];
        $this->small_image_x = $vars['small_image_x'];
        $this->small_image_y = $vars['small_image_y'];
        $this->small_image_format = $vars['small_image_format'];
        $this->large_image = $vars['large_image'];
        $this->large_image_x = $vars['large_image_x'];
        $this->large_image_y = $vars['large_image_y'];
        $this->large_image_size = $vars['large_image_size'];
        $this->large_image_format = $vars['large_image_format'];
        $this->large_image_filename = $vars['large_image_filename'];
        $this->up = $vars['up'];
        $this->subject = $vars['subject'];
        $this->comment0 = $vars['comment0'];
        $this->comment0_xml = anyToXML($this->comment0, $this->body_format,
                                       MTEXT_LINE);
        $this->comment1 = $vars['comment1'];
        $this->comment1_xml = anyToXML($this->comment1, $this->body_format,
                                       MTEXT_LINE);
        $this->author = $vars['author'];
        $this->author_xml = anyToXML($this->author, $this->body_format,
                                     MTEXT_LINE);
        $this->source = $vars['source'];
        $this->source_xml = anyToXML($this->source, $this->body_format,
                                     MTEXT_LINE);
        $this->title = $vars['title'];
        $this->title_xml = anyToXML($this->title, $this->body_format,
                                    MTEXT_LINE);
        $this->guest_login = $vars['guest_login'];
        $this->login = $vars['login'];
        if ($vars['user_name'] != '')
            $this->login = $vars['user_name'];
        $this->group_login = $vars['group_login'];
        if ($vars['group_name'] != '')
            $this->group_login = $vars['group_name'];
        $this->perm_string = $vars['perm_string'];
        if ($this->perm_string != '')
            $this->perms = permString($this->perm_string,
                                      strPerms($this->perms));
        else
            if ($vars['hidden'])
                $this->perms &= ~0x1100;
            else
                $this->perms |= 0x1100;
        $this->lang = $vars['lang'];
        $this->disabled = $vars['disabled'];
        $this->url = $vars['url'];
        $this->url_domain = getURLDomain($this->url);
        $this->parent_id = $vars['parent_id'];
        if ($this->up <= 0)
            $this->up = $this->parent_id;
        else
            if (getTypeByEntryId($this->up) == ENT_FORUM)
                $this->parent_id = getParentIdByEntryId($this->up);
    }

    public function getParentType() {
        return $this->parent_type;
    }

    public function getParentHref() {
        global $forumParentHref;

        $href = isset($forumParentHref[$this->getParentType()])
                ? $forumParentHref[$this->getParentType()]
                : $forumParentHref[ENT_NULL];
        return $this->getCompositeValue($href);
    }

}

function forumPermFilter($right, $prefix = '', $asGuest = false) {
    global $userModerator, $userId;

    $eUserId = !$asGuest ? $userId : 0;
    $eUserModerator = !$asGuest ? $userModerator : 0;

    if ($eUserModerator)
        return '1';
    $filter = permFilter($right, $prefix, $asGuest);
    if ($prefix != '' && substr($prefix, -1) != '.')
        $prefix .= '.';
    return "$filter and (${prefix}disabled=0".
           ($eUserId > 0 ? " or ${prefix}user_id=$eUserId)" : ')');
}

function forumListFilter($parent_id) {
    $Filter = 'entry='.ENT_FORUM;
    $Filter .= ' and '.forumPermFilter(PERM_READ);
    $Filter .= " and parent_id=$parent_id";
    return $Filter;
}

class ForumListIterator
        extends LimitSelectIterator {

    private $parent_type;

    public function __construct($parent_id = 0, $limit = 10, $offset = 0,
                                $sort = SORT_SENT, $disabled = -1) {
        if ($parent_id > 0) {
            $this->parent_type = getTypeByEntryId($parent_id);
            $Filter = forumListFilter($parent_id);
        } else {
            $this->parent_type = ENT_NULL;
            $Filter = '1';
        }
        if ($disabled >= 0)
            if ($disabled)
                $Filter .= " and entries.disabled<>0";
            else
                $Filter .= " and entries.disabled=0";
        $Order = getOrderBy($sort,
                    array(SORT_SENT  => 'entries.sent desc',
                          SORT_RSENT => 'entries.sent asc'));
        parent::__construct(
            'Forum',
            "select entries.id as id,subject,author,author_xml,body,body_xml,
                    body_format,sent,entries.created as created,
                    entries.modified as modified,guest_login,user_id,group_id,
                    perms,disabled,parent_id,users.login as login,
                    users.gender as gender,users.email as email,
                    users.hide_email as hide_email,users.hidden as user_hidden,
                    users.guest as user_guest
             from entries
                  left join users
                       on entries.user_id=users.id
             where $Filter
             $Order",
            $limit, $offset,
            "select count(*)
             from entries
             where $Filter");
    }

    protected function create(array $row) {
        if ($this->parent_type != ENT_NULL)
            $row['parent_type'] = $this->parent_type;
        else
            $row['parent_type'] = getTypeByEntryId($row['parent_id']);
        return parent::create($row);
    }

}

function storeForum(Forum $forum) {
    global $userId, $realUserId, $forumPremoderate;

    $vars = array(
        'entry' => $forum->getEntry(),
        'modified' => sqlNow(),
        'modifier_id' => $userId > 0 ? $userId : $realUserId,
        'subject' => $forum->getSubject(),
        'author' => $forum->getAuthor(),
        'author_xml' => $forum->getAuthorXML(),
        'body' => $forum->getBody(),
        'body_xml' => $forum->getBodyXML(),
        'body_format' => $forum->getBodyFormat(),
        'small_image' => $forum->getSmallImage(),
        'small_image_x' => $forum->getSmallImageX(),
        'small_image_y' => $forum->getSmallImageY(),
        'small_image_format' => $forum->getSmallImageFormat(),
        'large_image' => $forum->getLargeImage(),
        'large_image_x' => $forum->getLargeImageX(),
        'large_image_y' => $forum->getLargeImageY(),
        'large_image_size' => $forum->getLargeImageSize(),
        'large_image_format' => $forum->getLargeImageFormat(),
        'large_image_filename' => $forum->getLargeImageFilename(),
        'guest_login' => $forum->getGuestLogin(),
        'user_id' => $forum->getUserId(),
        'group_id' => $forum->getGroupId(),
        'perms' => $forum->getPerms(),
        'up' => $forum->getUpValue(),
        'parent_id' => $forum->getParentId()
    );
    if ($userModerator)
        $vars = array_merge(
            $vars,
            array(
                'disabled' => $forum->isDisabled(),
                'priority' => $forum->getPriority()
            )
        );
    else
        if ($forumPremoderate && $forum->getId() <= 0)
            $vars['disabled'] = true;
    if ($forum->getId()) {
        $forum->setTrack(trackById('entries', $forum->getId()));
        $result = sql(sqlUpdate('entries',
                                $vars,
                                array('id' => $forum->getId())),
                      __FUNCTION__, 'update');
        updateCatalogs($forum->getTrack());
        replaceTracksToUp('entries', $forum->getTrack(), $forum->getUpValue(),
                          $forum->getId());
    } else {
        $vars['sent'] = sqlNow();
        $vars['created'] = sqlNow();
        $vars['creator_id'] = $vars['modifier_id'];
        $vars['track'] = (string) time();
        $result = sql(sqlInsert('entries',
                                $vars),
                      __FUNCTION__, 'insert');
        $forum->setId(sql_insert_id());
        createTrack('entries', $forum->getId());
        updateCatalogs(trackById('entries', $forum->getId()));
    }
    answerUpdate($forum->getParentId());
    incContentVersions('forums');
    return $result;
}

function forumExists($id) {
    $hide = forumPermFilter(PERM_READ);
    $result = sql("select id
                   from entries
                   where id=$id and entry=".ENT_FORUM." and $hide",
                  __FUNCTION__);
    return mysql_num_rows($result) > 0;
}

function getForumById($id, $parent_id = 0, $quote = '', $quoteWidth = 77) {
    global $userId, $realUserId;

    $hide = forumPermFilter(PERM_READ);
    $result = sql("select entries.id as id,track,subject,author,author_xml,body,
                          body_xml,body_format,guest_login,user_id,group_id,
                          perms,small_image,small_image_x,small_image_y,
                          small_image_format,large_image,large_image_x,
                          large_image_y,large_image_size,large_image_format,
                          large_image_filename,up,parent_id,disabled,sent,
                          entries.created as created,
                          entries.modified as modified,users.login as login,
                          users.gender as gender,users.email as email,
                          users.hide_email as hide_email,
                          users.hidden as user_hidden,users.guest as user_guest
                   from entries
                        left join users
                             on entries.user_id=users.id
                   where entries.id=$id and $hide",
                  __FUNCTION__);
    if (mysql_num_rows($result) > 0) {
        $row = mysql_fetch_assoc($result);
        $row['parent_type'] = getTypeByEntryId($row['parent_id']);
        setCachedValue('track', 'entries', $row['id'], $row['track']);
        return new Forum($row);
    } else {
        global $rootForumPerms;

        if ($parent_id > 0) {
            $perms = getPermsById($parent_id);
            $group_id = $perms->getGroupId();
        } else {
            $group_id = 0;
        }
        return new Forum(array(
            'parent_id'   => $parent_id,
            'parent_type' => getTypeByEntryId($parent_id),
            'body'        => $quote != '' ? getQuote($quote, $quoteWidth) : '',
            'user_id'     => $userId > 0 ? $userId : $realUserId,
            'group_id'    => $group_id,
            'perms'       => $rootForumPerms
        ));
    }
}

function getForumListOffset($parent_id, $id, $sort = SORT_SENT) {
    $Filter = forumListFilter($parent_id);
    $conds = array(SORT_SENT  => array('field' => 'sent',
                                       'condition' => "sent > '%s'"),
                   SORT_RSENT => array('field' => 'sent',
                                       'condition' => "sent < '%s'"));
    $field = $conds[$sort]['field'];
    $result = sql("select $field
                   from entries
                   where id=$id",
                  __FUNCTION__, 'find');
    $value = mysql_num_rows($result) > 0 ? mysql_result($result, 0, 0) : 0;
    $Filter .= ' and '.sprintf($conds[$sort]['condition'], $value);
    $result = sql("select count(*)
                   from entries
                   where $Filter",
                  __FUNCTION__, 'count');
    return mysql_num_rows($result) > 0 ? mysql_result($result, 0, 0) : 0;
}

function deleteForum($id) {
    $forum = getForumById($id);
    $up = $forum->getUpValue();
    sql("update entries
         set up=$up
         where up=$id",
        __FUNCTION__, 'children');
    sql("delete from entries
         where id=$id",
        __FUNCTION__, 'delete');
    updateCatalogs($forum->getTrack());
    replaceTracks('entries', $forum->getTrack(), trackById('entries', $up));
    answerUpdate($forum->getParentId());
    incContentVersions('forums');
}

function renewForum($id) {
    renewEntry($id);
    $parent_id = getParentByEntryId($id);
    answerUpdate($parent_id);
    incContentVersions('forums');
}
?>
