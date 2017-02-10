<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/array.php');
require_once('lib/bug.php');
require_once('lib/cache.php');
require_once('lib/charsets.php');
require_once('lib/entries.php');
require_once('lib/grpentry.php');
require_once('lib/grpiterator.php');
require_once('lib/grps.php');
require_once('lib/ident.php');
require_once('lib/permissions.php');
require_once('lib/postings-info.php');
require_once('lib/select.php');
require_once('lib/selectiterator.php');
require_once('lib/sql.php');
require_once('lib/tmptexts.php');
require_once('lib/track.php');
require_once('lib/modbits.php');
require_once('lib/utils.php');
require_once('lib/text-any.php');
require_once('lib/html-cache.php');

class Topic
        extends GrpEntry {

    protected $full_name;
    protected $postings_info;
    protected $sub_count;

    public function __construct(array $row = array()) {
        global $rootTopicModbits, $tfRegular;

        $this->entry = ENT_TOPIC;
        $this->body_format = $tfRegular;
        $this->modbits = $rootTopicModbits;
        parent::__construct($row);
    }

    public function setup($vars) {
        global $tfRegular;

        if (!isset($vars['edittag']) || !$vars['edittag'])
            return;
        $this->body_format = $tfRegular;
        $this->body = $vars['body'];
        $this->body_xml = anyToXML($this->body, $this->body_format,
                                   MTEXT_SHORT);
        $this->up = $vars['up'];
        $this->subject = $vars['subject'];
        $this->comment0 = $vars['comment0'];
        $this->comment0_xml = anyToXML($this->comment0, $this->body_format,
                                       MTEXT_LINE);
        $this->comment1 = $vars['comment1'];
        $this->comment1_xml = anyToXML($this->comment1, $this->body_format,
                                       MTEXT_LINE);
        $this->ident = $vars['ident'] != '' ? $vars['ident'] : null;
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
        $this->modbits = disjunct($vars['modbits']);
        $this->index2 = $vars['index2'];
        $this->grps = array();
        foreach ($vars['grps'] as $grp)
            if (isGrpValid($grp))
                $this->grps[] = $grp;
    }

    public function getNbSubject() {
        return str_replace(' ', '&nbsp;', $this->getSubject());
    }

    public function getFullName() {
        return $this->full_name;
    }

    public function getFullNameShort() {
        global $fullNameShortSize;

        $s = $this->getFullName();
        return strlen($s) > $fullNameShortSize
               ? '...'.substr($s, -($fullNameShortSize - 3))
               : $s;
    }

    public function getPostingsInfo() {
        return $this->postings_info;
    }

    public function setPostingsInfo($postings_info) {
        $this->postings_info = $postings_info;
    }

    public function getAnswers() {
        $info = $this->getPostingsInfo();
        return $info ? $info->getTotal() : parent::getAnswers();
    }

    public function getLastAnswer() {
        $info = $this->getPostingsInfo();
        return $info ? $info->getMaxSent() : parent::getLastAnswer();
    }

    public function getSubCount() {
        return $this->sub_count;
    }

    public function setSubCount($sub_count) {
        $this->sub_count = $sub_count;
    }

}

function topicsPermFilter($right,$prefix='',$asGuest=false)
{
global $userAdminTopics,$userModerator;

$eUserAdminTopics=!$asGuest ? $userAdminTopics : 0;
$eUserModerator=!$asGuest ? $userModerator : 0;

if($eUserAdminTopics && $right!=PERM_POST)
  return '1';
if($eUserModerator && $right==PERM_POST)
  return '1';
return permFilter($right,$prefix,$asGuest);
}

class TopicIterator
        extends LimitSelectIterator {

    protected function getWhere($grp, $up = 0, $prefix = '', $recursive = false,
                                $level = 1, $index2 = -1, $asGuest = false) {
        $hide = 'and '.topicsPermFilter(PERM_READ, $prefix, $asGuest);
        $parentFilter = $up >= 0
                        ? 'and '.subtree('entries', $up, $recursive, 'up')
                        : '';
        $grpFilter = $grp != GRP_ALL
                     ? 'and '.grpFilter($grp, 'grp', 'entry_grps')
                     : '';
        // TODO: Levels > 2 are not implemented. strlen(topics.track) must be checked.
        $levelFilter = $level <= 1
                       || $up < 0 ? '' : "and entries.id<>$up and up<>$up";
        $index2Filter = $index2 < 0 ? '' : "and index2=$index2";
        return " where entry=".ENT_TOPIC." $hide $parentFilter $grpFilter
                       $levelFilter $index2Filter ";
    }

    public function __construct($query, $limit = 0, $offset = 0) {
        parent::__construct('Topic', $query, $limit, $offset);
    }

}

class TopicListIterator
        extends TopicIterator {

    private $fields;
    private $grp;
    private $asGuest;

    public function __construct($grp, $up = 0, $sort = SORT_SUBJECT,
            $recursive = false, $level = 1, $fields = SELECT_GENERAL,
            $index2 = -1, $limit = 0, $offset = 0, $asGuest = false) {
        $this->fields = $fields;
        $this->grp = $grp;
        $this->asGuest = $asGuest;
        /* Select */
        $distinct = $grp != GRP_ALL ? 'distinct' : '';
        $Select = "$distinct entries.id as id,entries.ident as ident,
                   entries.up as up,entries.track as track,
                   entries.catalog as catalog,entries.subject as subject,
                   entries.comment0 as comment0,
                   entries.comment0_xml as comment0_xml,
                   entries.comment1 as comment1,
                   entries.comment1_xml as comment1_xml,entries.body as body,
                   entries.body_xml as body_xml,
                   entries.body_format as body_format,
                   entries.user_id as user_id,entries.group_id as group_id,
                   users.login as login,gusers.login as group_login,
                   entries.perms as perms,entries.grp as grp,
                   entries.index2 as index2,entries.answers as answers,
                   entries.last_answer as last_answer";
        /* From */
        $grpTable = $grp != GRP_ALL ? 'left join entry_grps
                                            on entry_grps.entry_id=entries.id'
                                    : '';
        $From = "entries
                 left join users
                      on entries.user_id=users.id
                 left join users as gusers
                      on entries.group_id=gusers.id
                 $grpTable";
        /* Where */
        $Where = $this->getWhere($grp, $up, 'entries.', $recursive, $level,
                                 $index2, $asGuest);
        /* Order */
        $Order = getOrderBy($sort,
                            array(SORT_SUBJECT         => 'subject',
                                  SORT_INDEX0          => 'index0',
                                  SORT_RINDEX0         => 'index0 desc',
                                  SORT_INDEX1          => 'index1',
                                  SORT_RINDEX1         => 'index1 desc',
                                  SORT_RINDEX2_RINDEX0 => 'index2 desc,index0 desc'));
        /* Query */
        parent::__construct("select $Select
                             from $From
                             $Where
                             $Order",
                             $limit, $offset);
    }

    protected function create(array $row) {
        $topic = parent::create($row);
        if (($this->fields & SELECT_GRPS) != 0)
            $topic->setGrps(getGrpsByEntryId($row['id']));
        if (($this->fields & SELECT_INFO) != 0)
            $topic->setPostingsInfo(getPostingsInfo(
                $this->grp, $row['id'], GRP_NONE, 0, false, $this->asGuest));
        return $topic;
    }

}

class TopicNamesIterator
        extends TopicIterator {

    private $names;
    private $up;
    private $delimiter;

    public function __construct($grp, $up = -1, $recursive = false,
            $delimiter = ' :: ', $nameRoot = -1, $onlyAppendable = false,
            $onlyPostable = false, $asGuest = false) {
        $this->nameRoot = $nameRoot;
        $this->delimiter = $delimiter;

        $distinct = $grp != GRP_ALL ? 'distinct' : '';
        $grpTable = $grp != GRP_ALL ? 'left join entry_grps
                                            on entry_grps.entry_id=entries.id'
                                    : '';
        $Where = $this->getWhere($grp, $up, '', $recursive, 1, -1, $asGuest);
        if ($onlyAppendable)
            $Where .= ' and '.permMask('perms',PERM_UA|PERM_GA|PERM_OA|PERM_EA);
        if ($onlyPostable)
            $Where .= ' and '.permMask('perms',PERM_UP|PERM_GP|PERM_OP|PERM_EP);
        parent::__construct("select $distinct id,ident,up,track,catalog,subject
                             from entries
                                  $grpTable
                             $Where
                             order by track");
    }

    protected function create(array $row) {
        if ($row['id'] != $this->nameRoot) {
            if ($row['up'] != 0 && $row['up'] != $this->nameRoot)
                $row['full_name'] = getTopicFullNameById(
                                        $row['up'], $this->nameRoot,
                                        $this->delimiter)
                                    .$this->delimiter.$row['subject'];
            else
                $row['full_name'] = $row['subject'];
        }
        $topic = parent::create($row);
        setCachedValue('name', 'entries', $row['id'], $topic);
        return $topic;
    }

}

class SortedTopicNamesIterator
        extends MArrayIterator {

    public function __construct($grp, $up = -1, $recursive = false,
            $delimiter = ' :: ', $nameRoot = -1, $onlyWritable = false,
            $onlyPostable = false, $asGuest = false) {
        $iterator = new TopicNamesIterator($grp, $up, $recursive, $delimiter,
                $nameRoot, $onlyWritable, $onlyPostable, $asGuest);
        $topics = array();
        foreach ($iterator as $item)
            $topics[$item->getFullName()] = $item;
        // FIXME ������ ������������� � �������
        setlocale(LC_COLLATE, 'ru_RU.KOI8-R');
        uksort($topics, 'strcoll');
        parent::__construct($topics);
    }

}

class TopicHierarchyIterator
        extends MArrayIterator {

    public function __construct($topic_id, $root = -1, $reverse = false) {
        $topics = array();
        for ($id = idByIdent($topic_id); $id > 0 && $id != $root;) {
            $topic = getTopicById($id);
            $topics[] = $topic;
            $id = $topic->getUpValue();
        }
        if (!$reverse)
            $topics = array_reverse($topics);
        parent::__construct($topics);
    }

}

function storeTopic(Topic $topic) {
    global $userId, $realUserId;

    $vars = array('entry' => $topic->getEntry(),
                  'ident' => $topic->getIdent(),
                  'up' => $topic->getUpValue(),
                  'subject' => $topic->getSubject(),
                  'comment0' => $topic->getComment0(),
                  'comment0_xml' => $topic->getComment0XML(),
                  'comment1' => $topic->getComment1(),
                  'comment1_xml' => $topic->getComment1XML(),
                  'user_id' => $topic->getUserId(),
                  'group_id' => $topic->getGroupId(),
                  'perms' => $topic->getPerms(),
                  'modbits' => $topic->getModbits(),
                  'index2' => $topic->getIndex2(),
                  'body' => $topic->getBody(),
                  'body_xml' => $topic->getBodyXML(),
                  'body_format' => $topic->getBodyFormat(),
                  'modified' => sqlNow(),
                  'modifier_id' => $userId>0 ? $userId : $realUserId);
    if ($topic->getId()) {
        $topic->setTrack(trackById('entries', $topic->getId()));
        $result = sql(sqlUpdate('entries',
                                $vars,
                                array('id' => $topic->getId())),
                      __FUNCTION__, 'update');
        updateCatalogs($topic->getTrack());
        replaceTracksToUp('entries', $topic->getTrack(), $topic->getUpValue(),
                          $topic->getId());
    } else {
        $vars['sent'] = sqlNow();
        $vars['created'] = sqlNow();
        $vars['creator_id'] = $vars['modifier_id'];
        $vars['track'] = (string) time();
        $result = sql(sqlInsert('entries',
                                $vars),
                      __FUNCTION__, 'insert');
        $topic->setId(sql_insert_id());
        createTrack('entries', $topic->getId());
        updateCatalogs(trackById('entries', $topic->getId()));
    }
    incContentVersions('topics');
    return $result;
}

function getModbitsByTopicId($id) {
    global $rootTopicModbits;

    $hide = topicsPermFilter(PERM_READ);
    $result = sql("select modbits
                   from entries
                   where id=$id and $hide",
                  __FUNCTION__);
    return mysql_num_rows($result) > 0 ? mysql_result($result, 0, 0)
                                       : $rootTopicModbits;
}

function getTopicById($id, $up = 0, $fields = SELECT_GENERAL,
                      $asGuest = false) {
    global $userId, $userLogin, $userModerator, $rootTopicModbits,
           $rootTopicGroupName, $rootTopicPerms;

    if (hasCachedValue('obj', 'entries', $id))
         return getCachedValue('obj', 'entries', $id);
    $mhide = $userModerator ? 2 : 1;
    $hide = topicsPermFilter(PERM_READ, 'entries');
    $result = sql(
        "select entries.id as id,entries.up as up,entries.track as track,
                entries.catalog as catalog,entries.subject as subject,
                entries.comment0 as comment0,
                entries.comment0_xml as comment0_xml,
                entries.comment1 as comment1,
                entries.comment1_xml as comment1_xml,entries.body as body,
                entries.body_xml as body_xml,entries.body_format as body_format,
                entries.grp as grp,entries.modbits as modbits,
                entries.ident as ident,entries.user_id as user_id,
                users.login as login,users.gender as gender,
                users.email as email,users.hide_email as hide_email,
                entries.group_id as group_id,gusers.login as group_login,
                entries.perms as perms,entries.index2 as index2
         from entries
              left join users
                   on entries.user_id=users.id
              left join users as gusers
                   on entries.group_id=gusers.id
         where entries.id=$id and $hide",
        __FUNCTION__);
    if (mysql_num_rows($result) > 0) {
        $row = mysql_fetch_assoc($result);
        if (!is_null($row['ident']))
            setCachedValue('ident', 'entries', $row['ident'], $row['id']);
        $topic = new Topic($row); 
        if (($fields & SELECT_TOPICS) != 0)
            $topic->setSubCount(getSubtopicsCountById($id));
        if (($fields & SELECT_GRPS) != 0)
            $topic->setGrps(getGrpsByEntryId($id));
        if (($fields & SELECT_INFO) != 0)
            $topic->setPostingsInfo(
                getPostingsInfo(GRP_ALL, $id, GRP_NONE, 0, false, $asGuest));
        setCachedValue('obj', 'entries', $id, $topic);
    } else {
        if ($up > 0) {
            $topic = getTopicById($up, 0, SELECT_GENERAL | SELECT_GRPS);
            $modbits = $topic->getModbits() & ~(MODT_ROOT | MODT_TRANSPARENT);
            $topic = new Topic(array(
                'up'          => $topic->getId(),
                'grps'        => $topic->getGrps(),
                'modbits'     => $modbits,
                'user_id'     => $userId,
                'login'       => $userLogin,
                'group_id'    => $topic->getGroupId(),
                'group_login' => $topic->getGroupLogin(),
                'perms'       => $topic->getPerms()));
        } else {
            $topic = new Topic(array(
                'grps'        => grpArray(GRP_ALL),
                'modbits'     => $rootTopicModbits,
                'user_id'     => $userId,
                'login'       => $userLogin,
                'group_id'    => getUserIdByLogin($rootTopicGroupName),
                'group_login' => $rootTopicGroupName,
                'perms'       => $rootTopicPerms));
        }
    }
    return $topic;
}

function getTopicNameById($id) {
    if (hasCachedValue('name', 'entries', $id))
        return getCachedValue('name', 'entries', $id);
    $hide = topicsPermFilter(PERM_READ);
    $result = sql("select id,up,subject
                   from entries
                   where id=$id and $hide",
                  __FUNCTION__);
    $topic = new Topic(mysql_num_rows($result) > 0 ? mysql_fetch_assoc($result)
                                                   : array());
    setCachedValue('name', 'entries', $id, $topic);
    return $topic;
}

function getTopicFullNameById($id, $root = 0, $delimiter = ' :: ') {
    if ($id == $root)
        return '';
    $topic = getTopicNameById($id);
    if ($topic->getUpValue() != 0 && $topic->getUpValue() != $root)
        return getTopicFullNameById($topic->getUpValue(), $root, $delimiter).
               $delimiter.$topic->getSubject();
    else
        return $topic->getSubject();
}

function getSubtopicsCountById($id, $recursive = false) {
    $id = idByIdent($id);
    $result = sql('select count(*)
                   from entries
                   where entry='.ENT_TOPIC.' and '
                                .subtree('entries', $id, $recursive, 'up'),
                  __FUNCTION__);
    return mysql_num_rows($result) > 0
           ? mysql_result($result, 0, 0) - ($recursive ? 1 : 0) : 0;
}

function topicExists($id) {
    $hide = topicsPermFilter(PERM_READ);
    $result = sql("select id
                   from entries
                   where id=$id and entry=".ENT_TOPIC." and $hide",
                  __FUNCTION__);
    return mysql_num_rows($result) > 0;
}

function topicHasContent($id) {
    $result = sql("select count(*)
                   from entries
                   where (parent_id=$id or up=$id)
                         and (entry=".ENT_TOPIC.' or entry='.ENT_POSTING.')',
                  __FUNCTION__);
    return mysql_num_rows($result) > 0
           ? mysql_result($result, 0, 0) > 0 : false;
}

function deleteTopic($id, $destid) {
    $oldTrack = trackById('entries', $id);
    sql("delete from entries
         where id=$id",
        __FUNCTION__, 'delete_topic');
    sql("delete from cross_entries
         where source_id=$id or peer_id=$id",
        __FUNCTION__, 'delete_cross_topics');
    incContentVersions('topics');
    if ($destid <= 0)
        return;
    sql("update entries
         set up=$destid
         where up=$id",
        __FUNCTION__, 'update_up');
    sql("update entries
         set parent_id=$destid
         where parent_id=$id",
        __FUNCTION__, 'update_parent_id');
    updateCatalogs($oldTrack.' ');
    replaceTracks('entries', $oldTrack.' ', trackById('entries', $destid).' ');
    incContentVersions('postings');
}
?>