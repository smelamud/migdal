<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/alphabet.php');
require_once('lib/bug.php');
require_once('lib/cache.php');
require_once('lib/counters.php');
require_once('lib/forums.php');
require_once('lib/grps.php');
require_once('lib/limitselect.php');
require_once('lib/entries.php');
require_once('lib/grpentry.php');
require_once('lib/mtext-shorten.php');
require_once('lib/random.php');
require_once('lib/selectiterator.php');
require_once('lib/select.php');
require_once('lib/sort.php');
require_once('lib/sql.php');
require_once('lib/text.php');
require_once('lib/topics.php');
require_once('lib/track.php');
require_once('lib/users.php');
require_once('lib/votes.php');
require_once('lib/uri.php');
require_once('lib/time.php');
require_once('lib/text-any.php');
require_once('lib/html-cache.php');

class Posting
        extends GrpEntry {

    protected $counter_value0;
    protected $counter_value1;
    protected $co_ctr;

    public function __construct(array $row = array()) {
        global $tfRegular, $tfLarge;

        $this->entry = ENT_POSTING;
        $this->body_format = $tfRegular;
        $this->large_body_format = $tfLarge;
        parent::__construct($row);
    }

    public function setup($vars) {
        global $tfRegular, $tfLarge;

        if (!isset($vars['edittag']) || !$vars['edittag'])
            return;
        $this->body_format = $vars['body_format'];
        if (!c_digit($this->body_format) || $this->body_format > TF_MAX)
            $this->body_format = $tfRegular;
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
        $this->guest_login = isset($vars['guest_login'])
                                   ? $vars['guest_login'] : '';
        $this->login = isset($vars['login']) ? $vars['login'] : '';
        if (isset($vars['user_name']) && $vars['user_name'] != '')
            $this->login = $vars['user_name'];
        $this->group_login = isset($vars['group_login'])
                             ? $vars['group_login'] : '';
        if (isset($vars['group_name']) && $vars['group_name'] != '')
            $this->group_login = $vars['group_name'];
        $this->perm_string = isset($vars['perm_string'])
                             ? $vars['perm_string'] : '';
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
        $this->ident = $vars['ident'] != '' ? $vars['ident'] : null;
        $this->index1 = $vars['index1'];
        $this->index2 = $vars['index2'];
        $this->parent_id = $vars['parent_id'];
        $this->grp = $vars['grp'];
        $this->person_id = $vars['person_id'];
        $this->priority = $vars['priority'];
        if (!empty($vars['sent']))
            $this->sent = sqlDate($vars['sent']);
        $this->sent = sqlDate(composeDateTime($this->getSent(), $vars, 'sent'));
        if ($this->up <= 0)
            $this->up = $this->parent_id;
        else
            if (getTypeByEntryId($this->up) == ENT_POSTING)
                $this->parent_id = getParentIdByEntryId($this->up);
    }

    public function isShadow() {
        return $this->getId() != $this->getOrigId();
    }

    public function getTopicId() {
        return $this->getParentId();
    }

    public function getIssues() {
        $s = $this->getIndex1();
        if ($this->getIndex2() > 0)
            $s .= '-'.($this->getIndex1() + $this->getIndex2());
        return $s;
    }

    public function getCounterValue0() {
        return $this->counter_value0;
    }

    public function getCounterValue1() {
        return $this->counter_value1;
    }

    public function getCTR() {
        return 1 / $this->co_ctr;
    }

    public function isMandatory($field) {
        $editor = $this->getGrpEditor();
        foreach ($editor as $item)
            if ($item['ident'] == $field)
                return $item['mandatory'];
        return false;
    }

    public function getHeading($useURL = false) {
        $heading = '';
        if (method_exists($this, 'getGrpHeading'))
            $heading = $this->getGrpHeading();
        if ($heading == '' && method_exists($this, 'getSubject'))
            $heading = $this->getSubject();
        if ($heading == '' && method_exists($this, 'getBodyTiny'))
            $heading = $this->getBodyTiny();
        if ($heading == '' && $useURL
            && method_exists($this,'getGrpDetailsHref'))
            $heading = $this->getGrpDetailsHref();
        if ($heading == '')
            $heading = 'Без названия';
        return $heading;
    }

}

function postingsPermFilter($right,$prefix='',$asGuest=false)
{
global $userModerator,$userId;

$eUserId=!$asGuest ? $userId : 0;
$eUserModerator=!$asGuest ? $userModerator : 0;

if($eUserModerator)
  return '1';
$filter=permFilter($right,$prefix,$asGuest);
if($prefix!='' && substr($prefix,-1)!='.')
  $prefix.='.';
return "$filter and (${prefix}disabled=0".
       ($eUserId>0 ? " or ${prefix}user_id=$eUserId)" : ')');
}

function postingListFields($fields=SELECT_GENERAL)
{
$Fields='entries.id as id,entries.ident as ident,entries.up as up,
         entries.track as track,entries.catalog as catalog,
         entries.parent_id as parent_id,entries.orig_id as orig_id,
         entries.grp as grp,entries.person_id as person_id,
         entries.guest_login as guest_login,entries.user_id as user_id,
         entries.group_id as group_id,entries.perms as perms,
         entries.disabled as disabled,
         entries.subject as subject,entries.lang as lang,
         entries.author as author,entries.author_xml as author_xml,
         entries.source as source,entries.source_xml as source_xml,
         entries.title as title,entries.title_xml as title_xml,
         entries.comment0 as comment0,entries.comment0_xml as comment0_xml,
         entries.comment1 as comment1,entries.comment1_xml as comment1_xml,
         entries.url as url,entries.url_domain as url_domain,
         entries.url_check_success as url_check_success,entries.body as body,
         entries.body_xml as body_xml,entries.body_format as body_format,
         entries.body_format as body_format,
         entries.has_large_body as has_large_body,
         entries.large_body_format as large_body_format,
         entries.large_body_filename as large_body_filename,
         entries.priority as priority,entries.index0 as index0,
         entries.index1 as index1,entries.index2 as index2,
         entries.vote as vote,entries.vote_count as vote_count,
         entries.rating as rating,entries.sent as sent,
         entries.modified as modified,entries.modbits as modbits,
         entries.answers as answers,entries.last_answer as last_answer,
         entries.last_answer_id as last_answer_id,
         entries.last_answer_user_id as last_answer_user_id,
         entries.last_answer_guest_login as last_answer_guest_login,
         if(entries.answers!=0,entries.last_answer,entries.modified) as age,
         entries.small_image as small_image,
         entries.small_image_x as small_image_x,
         entries.small_image_y as small_image_y,
         entries.small_image_format as small_image_format,
         entries.large_image as large_image,
         entries.large_image_x as large_image_x,
         entries.large_image_y as large_image_y,
         entries.large_image_size as large_image_size,
         entries.large_image_format as large_image_format,
         entries.large_image_filename as large_image_filename,
         users.login as login,users.gender as gender,users.email as email,
         users.hide_email as hide_email,users.hidden as user_hidden,
         users.guest as user_guest';
if(($fields & SELECT_LARGE_BODY)!=0)
  $Fields.=',entries.large_body as large_body,
            entries.large_body_xml as large_body_xml';
if(($fields & SELECT_CTR)!=0)
  $Fields.=',counter0.value as counter_value0,
            counter1.value as counter_value1,
            if(counter1.value is null or counter1.value=0,
               1000000,counter0.value/counter1.value) as co_ctr,
            if((entries.perms & 0x1100)=0,1,0) as hidden';
return $Fields;
}

function origFields($fields=SELECT_GENERAL)
{
$Fields='entries.subject as subject,
         entries.author as author,entries.author_xml as author_xml,
         entries.source as source,entries.source_xml as source_xml,
         entries.title as title,entries.title_xml as title_xml,
         entries.comment0 as comment0,entries.comment0_xml as comment0_xml,
         entries.comment1 as comment1,entries.comment1_xml as comment1_xml,
         entries.url as url,entries.url_domain as url_domain,
         entries.url_check_success as url_check_success,entries.body as body,
         entries.body_xml as body_xml,entries.body_format as body_format,
         entries.has_large_body as has_large_body,
         entries.large_body_format as large_body_format,
         entries.large_body_filename as large_body_filename,
         entries.small_image as small_image,
         entries.small_image_x as small_image_x,
         entries.small_image_y as small_image_y,
         entries.small_image_format as small_image_format,
         entries.large_image as large_image,
         entries.large_image_x as large_image_x,
         entries.large_image_y as large_image_y,
         entries.large_image_size as large_image_size,
         entries.large_image_format as large_image_format,
         entries.large_image_filename as large_image_filename';
if(($fields & SELECT_LARGE_BODY)!=0)
  $Fields.=',entries.large_body as large_body,
            entries.large_body_xml as large_body_xml';
return $Fields;
}

function postingListTables($fields=SELECT_GENERAL,$sort=SORT_SENT)
{
$Tables='entries
         left join users
              on entries.user_id=users.id';
if(($fields & SELECT_CTR)!=0)
  $Tables.=' left join counters as counter0
                  on entries.orig_id=counter0.entry_id and counter0.serial=1
                     and counter0.mode='.CMODE_EAR_HITS.'
             left join counters as counter1
                  on entries.orig_id=counter1.entry_id and counter1.serial=1
                     and counter1.mode='.CMODE_EAR_CLICKS;
if($sort==SORT_TOPIC_INDEX0_INDEX0)
  $Tables.=' left join entries as topics
                  on entries.parent_id=topics.id';
return $Tables;
}

function origTables($fields=SELECT_GENERAL)
{
$Tables='entries';
return $Tables;
}

function postingListGrpFilter($grp,$withAnswers=GRP_NONE)
{
$grp=grpArray($grp);
$withAnswers=grpArray($withAnswers);
$conds=array();
foreach($withAnswers as $g)
       $conds[]="entries.grp=$g and entries.answers<>0";
foreach($grp as $g)
       if(!in_array($g,$withAnswers))
         $conds[]="entries.grp=$g";
return count($conds)>0 ? '('.join(' or ',$conds).')' : '1';
}

function postingListTopicFilter($topic_id=-1,$recursive=false)
{
if($topic_id<0 || $topic_id==0 && $recursive)
  return '1';
if(!is_array($topic_id))
  $topic_id=array($topic_id);
if(!is_array($recursive))
  $recursive=array($recursive);
$conds=array();
for($i=0;$i<count($topic_id);$i++)
   if($topic_id[$i]>0 || $topic_id[$i]==0 && !$recursive[$i])
     $conds[]='entries.'.subtree('entries',$topic_id[$i],$recursive[$i]);
return count($conds)>0 ? '('.join(' or ',$conds).')' : '1';
}

function postingListFilter($grp,$topic_id=-1,$recursive=false,$person_id=-1,
                           $sort=SORT_SENT,$withAnswers=GRP_NONE,$user=0,
                           $index1=-1,$later=0,$up=-1,$fields=SELECT_GENERAL,
                           $modbits=MOD_NONE,$hidden=-1,$disabled=-1,$prefix='',
                           $withIdent=false,$earlier=0,$asGuest=false)
{
$Filter='entries.entry='.ENT_POSTING;
$Filter.=' and '.postingsPermFilter(PERM_READ,'entries',$asGuest);
$Filter.=' and '.postingListGrpFilter($grp,$withAnswers);
$Filter.=' and '.postingListTopicFilter($topic_id,$recursive);
if($person_id>=0)
  $Filter.=" and entries.person_id=$person_id";
if($user>0)
  $Filter.=" and entries.user_id=$user";
if($index1>=0)
  switch($sort)
        {
        case SORT_INDEX1:
             $Filter.=" and entries.index1>=$index1";
             break;
        case SORT_RINDEX1:
             $Filter.=" and entries.index1<=$index1";
             break;
        default;
             $Filter.=" and entries.index1=$index1";
        }
if($later>0)
  $Filter.=" and unix_timestamp(entries.sent)>=$later";
if($earlier>0)
  $Filter.=" and unix_timestamp(entries.sent)<$earlier";
if($up>0)
  $Filter.=" and entries.up=$up";
if($modbits>0)
  $Filter.=" and (entries.modbits & $modbits)<>0";
if($hidden>=0)
  if($hidden)
    $Filter.=" and (entries.perms & 0x1100)=0";
  else
    $Filter.=" and (entries.perms & 0x1100)<>0";
if($disabled>=0)
  if($disabled)
    $Filter.=" and entries.disabled<>0";
  else
    $Filter.=" and entries.disabled=0";
if($prefix!='')
  {
  $lprefix=strtolower($prefix);
  $prefixFilters=array(SORT_NAME       => " and entries.subject like '$prefix%'",
                       SORT_URL_DOMAIN => " and entries.url_domain like '$lprefix%'
                                            and entries.url_domain<>''");
  $Filter.=@$prefixFilters[$sort]!='' ? $prefixFilters[$sort] : '';
  }
if($withIdent)
  $Filter.=" and entries.ident<>''";
return $Filter;
}

class PostingListIterator
        extends LimitSelectIterator {

    private $fields;
    private $where;

    public function __construct($grp, $topic_id = -1, $recursive = false,
            $limit = 10, $offset = 0, $person_id = -1, $sort = SORT_SENT,
            $withAnswers = GRP_NONE, $user = 0, $index1 = -1, $later = 0,
            $up = -1, $showShadows = true, $fields = SELECT_GENERAL,
            $modbits = MOD_NONE, $hidden = -1, $disabled = -1, $prefix = '',
            $withIdent = false, $earlier = 0, $asGuest = false) {
        if ($sort == SORT_CTR)
            $fields |= SELECT_CTR;
        $this->fields = $fields;

        $Select = $showShadows ? postingListFields($this->fields)
                               : 'distinct entries.orig_id';
        $SelectCount = $showShadows ? 'count(*)'
                                    : 'count(distinct entries.orig_id)';
        $From = postingListTables($this->fields, $sort);
        $this->where = postingListFilter($grp, $topic_id, $recursive,
                $person_id, $sort, $withAnswers, $user, $index1, $later, $up,
                $fields, $modbits, $hidden, $disabled, $prefix, $withIdent,
                $earlier, $asGuest);
        $Order = getOrderBy($sort,
            array(SORT_SENT       => 'entries.sent desc',
                  SORT_NAME       => 'entries.subject',
                  SORT_ACTIVITY   => 'if(entries.answers!=0,entries.last_answer,entries.sent) desc',
                  SORT_CTR        => 'hidden asc,co_ctr asc,counter_value0 asc',
                  SORT_INDEX0     => 'entries.index0',
                  SORT_INDEX1     => 'entries.index1',
                  SORT_RINDEX1    => 'entries.index1 desc',
                  SORT_RATING     => 'entries.rating desc,entries.vote_count desc,
                                      entries.sent desc',
                  SORT_URL_DOMAIN => 'entries.url_domain,entries.url',
                  SORT_TOPIC_INDEX0_INDEX0
                                  => 'topics.index0,entries.index0',
                  SORT_RSENT      => 'entries.sent asc'));
        parent::__construct('Posting',
                            "select $Select
                             from $From
                             where {$this->where}
                             $Order",
                            $limit, $offset,
                            "select $SelectCount
                             from $From
                             where {$this->where}");
    }

    protected function create(array $row) {
        if ((!isset($row['id']) || $row['id'] <= 0) && $row['orig_id'] > 0) {
            $Select = postingListFields($this->fields);
            $From = postingListTables($this->fields);
            $Where = "{$this->where} and entries.orig_id={$row['orig_id']}";
            $result = sql("select $Select
                           from $From
                           where $Where
                           order by entries.id",
                          __FUNCTION__, 'shadow');
            $shadow = mysql_num_rows($result) > 0 ? mysql_fetch_assoc($result)
                                                  : array();
            $row = array_merge($row, $shadow);
        }
        if ($row['id'] != $row['orig_id']) {
            $Select = origFields($this->fields);
            $From = origTables($this->fields);
            $Where = "entries.id={$row['orig_id']}";
            $result = sql("select $Select
                           from $From
                           where $Where",
                          __FUNCTION__, 'original');
            $orig = mysql_num_rows($result) > 0 ? mysql_fetch_assoc($result)
                                                : array();
            $row=array_merge($row,$orig);
        }
        if ($row['id'] > 0) {
            if ($row['ident'] != '')
                setCachedValue('ident', 'entries', $row['ident'], $row['id']);
            setCachedValue('track', 'entries', $row['id'], $row['track']);
            setCachedValue('catalog', 'entries', $row['id'], $row['catalog']);
        }
        $posting = parent::create($row);
        if ($row['id'] > 0)
            setCachedValue('obj', 'entries', $row['id'], $posting);
        return $posting;
    }

}

class PostingUsersIterator
        extends SelectIterator {

    public function __construct($grp = GRP_ALL, $topic_id = -1,
                                $recursive = false, $asGuest = false) {
        $hide = postingsPermFilter(PERM_READ, 'entries', $asGuest);
        $grpFilter = postingListGrpFilter($grp);
        $topicFilter = postingListTopicFilter($topic_id, $recursive);
        parent::__construct(
               'User',
               "select distinct users.id as id,login,gender,email,hide_email,
                                users.hidden as user_hidden,users.name as name,
                                jewish_name,surname
                from users
                     left join entries
                          on entries.user_id=users.id
                where $hide and $grpFilter and $topicFilter
                order by surname,jewish_name,name");
    }

}

class PostingAlphabetIterator
        extends AlphabetIterator {

    public function __construct($limit = 0, $sort = SORT_URL_DOMAIN,
                                $topic_id = -1, $recursive = false,
                                $grp = GRP_ALL, $showShadows = false) {
        $hide = 'and '.postingsPermFilter(PERM_READ);
        $fields = array(SORT_NAME       => 'subject',
                        SORT_URL_DOMAIN => 'url_domain');
        $field = @$fields[$sort] != '' ? $fields[$sort] : 'url';
        $prefixFilters = array(SORT_NAME       => "and subject like '@prefix@%'",
                               SORT_URL_DOMAIN => "and url_domain like '@prefix@%'
                                                   and url_domain<>''");
        $prefixFilter = @$prefixFilters[$sort] != '' ? $prefixFilters[$sort]
                                                     : " and url like '@prefix@%'";
        $order = getOrderBy($sort,
                            array(SORT_NAME       => 'subject',
                                  SORT_URL_DOMAIN => 'url_domain,url'));
        $topicFilter = $topic_id>=0
                       ? 'and '.subtree('entries', $topic_id, $recursive) : '';
        $grpFilter = 'and '.grpFilter($grp);
        $shadowFilter = !$showShadows ? 'and id=orig_id' : '';
        parent::__construct(
            "select left($field,@len@) as letter,1 as count
             from entries
             where entry=".ENT_POSTING." $hide $topicFilter $grpFilter
                   $shadowFilter $prefixFilter
             $order");
    }

}

const SPF_ORIGINAL = 1;
const SPF_DUPLICATE = 2;
const SPF_SHADOW = 4;
define('SPF_ALL', SPF_ORIGINAL | SPF_DUPLICATE | SPF_SHADOW);

function storePostingFields(Posting $posting, $fields) {
    global $userId, $realUserId, $userModerator;

    $vars = array(
        'entry' => $posting->getEntry(),
        'modified' => sqlNow()
    );
    if (($fields & SPF_ORIGINAL) != 0)
        $vars = array_merge($vars, array(
            'subject' => $posting->getSubject(),
            'author' => $posting->getAuthor(),
            'author_xml' => $posting->getAuthorXML(),
            'source' => $posting->getSource(),
            'source_xml' => $posting->getSourceXML(),
            'title' => $posting->getTitle(),
            'title_xml' => $posting->getTitleXML(),
            'comment0' => $posting->getComment0(),
            'comment0_xml' => $posting->getComment0XML(),
            'comment1' => $posting->getComment1(),
            'comment1_xml' => $posting->getComment1XML(),
            'url' => $posting->getURL(),
            'url_domain' => $posting->getURLDomain(),
            'body' => $posting->getBody(),
            'body_xml' => $posting->getBodyXML(),
            'body_format' => $posting->getBodyFormat(),
            'has_large_body' => $posting->hasLargeBody(),
            'large_body' => $posting->getLargeBody(),
            'large_body_xml' => $posting->getLargeBodyXML(),
            'large_body_format' => $posting->getLargeBodyFormat(),
            'large_body_filename' => $posting->getLargeBodyFilename(),
            'small_image' => $posting->getSmallImage(),
            'small_image_x' => $posting->getSmallImageX(),
            'small_image_y' => $posting->getSmallImageY(),
            'small_image_format' => $posting->getSmallImageFormat(),
            'large_image' => $posting->getLargeImage(),
            'large_image_x' => $posting->getLargeImageX(),
            'large_image_y' => $posting->getLargeImageY(),
            'large_image_size' => $posting->getLargeImageSize(),
            'large_image_format' => $posting->getLargeImageFormat(),
            'large_image_filename' => $posting->getLargeImageFilename()
        ));
    if (($fields & SPF_DUPLICATE) != 0) {
        $vars = array_merge($vars, array(
            'person_id' => $posting->getPersonId(),
            'guest_login' => $posting->getGuestLogin(),
            'user_id' => $posting->getUserId(),
            'group_id' => $posting->getGroupId(),
            'perms' => $posting->getPerms(),
            'lang' => $posting->getLang(),
            'index1' => $posting->getIndex1(),
            'index2' => $posting->getIndex2()
        ));
        if ($posting->getId() <= 0)
            $vars = array_merge($vars, array(
                'sent' => sqlNow()
            ));
        if ($userModerator)
            $vars = array_merge($vars, array(
                'disabled' => $posting->isDisabled(),
                'priority' => $posting->getPriority(),
                'sent' => sqlDate($posting->getSent())
            ));
    }
    if (($fields & SPF_SHADOW) != 0) {
        $vars = array_merge($vars, array(
            'up' => $posting->getUpValue(),
            'parent_id' => $posting->getParentId(),
            'grp' => $posting->getGrp(),
            'modifier_id' => $userId > 0 ? $userId : $realUserId));
        if ($userModerator)
            $vars = array_merge($vars, array(
                'ident' => $posting->getIdent()
            ));
    }
    return $vars;
}

function storePosting(Posting $posting) {
    if ($posting->getId()) {
        $posting->setTrack(trackById('entries', $posting->getId()));
        $vars = storePostingFields($posting, SPF_SHADOW);
        $result = sql(sqlUpdate('entries',
                                $vars,
                                array('id' => $posting->getId())),
                      __FUNCTION__, 'update_shadow');
        $vars = storePostingFields($posting, SPF_DUPLICATE);
        $result = sql(sqlUpdate('entries',
                                $vars,
                                array('orig_id' => $posting->getOrigId())),
                      __FUNCTION__, 'update_duplicate');
        $vars = storePostingFields($posting, SPF_ORIGINAL);
        $result = sql(sqlUpdate('entries',
                                $vars,
                                array('id' => $posting->getOrigId())),
                      __FUNCTION__, 'update_original');
        updateCatalogs($posting->getTrack());
        replaceTracksToUp('entries', $posting->getTrack(),
                          $posting->getUpValue(), $posting->getId());
        answerUpdate($posting->getId());
    } else {
        $vars = storePostingFields($posting, SPF_ALL);
        $vars['created'] = sqlNow();
        $vars['creator_id'] = $vars['modifier_id'];
        $vars['track'] = (string) time();
        $result = sql(sqlInsert('entries',
                      $vars),
            __FUNCTION__, 'insert');
        $posting->setId(sql_insert_id());
        setOrigIdToEntryId($posting);
        createTrack('entries', $posting->getId());
        updateCatalogs(trackById('entries', $posting->getId()));
    }
    incContentVersions('postings');
    return $result;
}

function getRootPosting($grp,$topic_id,$up,$index1=0)
{
global $userId,$realUserId,$rootPostingPerms;

if($up>0 && $up!=$topic_id)
  {
  $msg=getPermsById($up);
  $group_id=$msg->getGroupId();
  $perms=$msg->getPerms();
  }
else if($topic_id>0)
  {
  $topic=getPermsById($topic_id);
  $group_id=$topic->getGroupId();
  $perms=$rootPostingPerms;
  }
else
  {
  $group_id=0;
  $perms=$rootPostingPerms;
  }
return new Posting(array('id'        => 0,
                         'grp'       => $grp,
                         'parent_id' => $topic_id,
                         'up'        => $up>0 ? $up : $topic_id,
                         'user_id'   => $userId>0 ? $userId : $realUserId,
                         'group_id'  => $group_id,
                         'perms'     => $perms,
                         'index1'    => $index1,
                         'sent'      => sqlNow()));
}

function postingExists($id)
{
$hide=postingsPermFilter(PERM_READ);
$result=sql("select id
             from entries
             where id=$id and entry=".ENT_POSTING." and $hide",
            __FUNCTION__);
return mysql_num_rows($result)>0;
}

function getPostingById($id=-1,$grp=GRP_ALL,$topic_id=-1,$fields=SELECT_GENERAL,
                        $up=-1,$index1=0)
{
if($id=='' || $id<0)
  return getRootPosting($grp,$topic_id,$up,$index1);
if($fields==SELECT_GENERAL && hasCachedValue('obj','entries',$id))
  return getCachedValue('obj','entries',$id);
$Select=postingListFields($fields);
$From=postingListTables($fields);
$result=sql("select $Select
             from $From
             where entries.id=$id",
            __FUNCTION__,'shadow');
if(mysql_num_rows($result)>0)
  {
  $row=mysql_fetch_assoc($result);
  if($row['id']!=$row['orig_id'])
    {
    $Select=origFields($fields);
    $From=origTables($fields);
    $result=sql("select $Select
                 from $From
                 where entries.id={$row['orig_id']}",
                __FUNCTION__,'original');
    $orig=mysql_num_rows($result)>0 ? mysql_fetch_assoc($result) : array();
    $row=array_merge($row,$orig);
    }
  $posting=new Posting($row);
  setCachedValue('obj','entries',$id,$posting);
  return $posting;
  }
else
  return getRootPosting($grp,$topic_id,$up,$index1);
}

function getPostingId($grp=GRP_ALL,$index1=-1,$topic_id=-1)
{
/* эта функция может вызываться из structure.conf */
$index1=(int)$index1;
$topic_id=(int)$topic_id;

$Where='entry='.ENT_POSTING;
$Where.=' and '.postingsPermFilter(PERM_READ);
$Where.=' and '.postingListGrpFilter($grp);
$Where.=' and '.postingListTopicFilter($topic_id);
if($index1>=0)
  $Where.=" and index1=$index1";
$result=sql("select id
             from entries
             where $Where",
            __FUNCTION__);
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

function getVoteInfoByPostingId($id,$grp=GRP_ALL)
{
$result=sql("select id,vote,vote_count,rating
             from entries
             where id=$id",
            __FUNCTION__);
return mysql_num_rows($result)>0 ? new Posting(mysql_fetch_assoc($result))
                                 : new Posting(array('grp' => $grp));
}

define('SIBLING_ID',1);
define('SIBLING_INDEX',2);

define('SIBLING_UNDEF',-1);
define('SIBLING_EDGE',-2);

function getSibling($grp=GRP_ALL,$topic_id=-1,$up=-1,$index0=SIBLING_UNDEF,
                    $index1=SIBLING_UNDEF,$next=true,$field=SIBLING_ID)
{
if($index0!=SIBLING_UNDEF)
  {
  $indexField='index0';
  if($index0!=SIBLING_EDGE)
    $filter=$next ? "index0>$index0" : "index0<$index0";
  else
    $filter='1';
  if($index1!=SIBLING_UNDEF)
    $filter.=" and index1=$index1";
  }
else
  {
  $indexField='index1';
  if($index1!=SIBLING_EDGE)
    $filter=$next ? "index1>$index1" : "index1<$index1";
  else
    $filter='1';
  }
$filter.=' and '.postingsPermFilter(PERM_READ);
$filter.=' and '.postingListGrpFilter($grp);
$filter.=' and '.postingListTopicFilter($topic_id,false);
if($up>0)
  $filter.=" and up=$up";
$order=$next ? 'asc' : 'desc';
$select=$field==SIBLING_ID ? 'id' : $indexField;
$result=sql("select $select
             from entries
             where $filter
             order by $indexField $order
             limit 1",
            __FUNCTION__);
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

function getSiblingId($grp=GRP_ALL,$topic_id=-1,$up=-1,$index0=SIBLING_UNDEF,
                      $index1=SIBLING_UNDEF,$next=true)
{
return getSibling($grp,$topic_id,$up,$index0,$index1,$next,SIBLING_ID);
}

function getSiblingIndex($grp=GRP_ALL,$topic_id=-1,$up=-1,$index0=SIBLING_UNDEF,
                         $index1=SIBLING_UNDEF,$next=true)
{
return getSibling($grp,$topic_id,$up,$index0,$index1,$next,SIBLING_INDEX);
}

function getPostingDomainCount($topic_id=-1,$recursive=false,$grp=GRP_ALL)
{
$hide='and '.postingsPermFilter(PERM_READ);
$topicFilter=$topic_id>=0 ? 'and '.subtree('entries',$topic_id,$recursive) : '';
$grpFilter='and '.grpFilter($grp);
$result=sql("select count(distinct url_domain)
             from entries
             where entry=".ENT_POSTING." and id=orig_id $hide $topicFilter
                   $grpFilter",
            __FUNCTION__);
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

function isModbitRequired($topicModbits,$bit,$posting)
{
global $userId,$userModerator;

$required=($topicModbits & $bit)!=0;
switch($bit)
      {
      case MODT_PREMODERATE:
           $required=($posting->getId()==0 || $posting->isDisabled())
                     && $required && $userId>0 && !$userModerator;
           break;
      case MODT_MODERATE:
           $required=$required && !$userModerator;
           break;
      case MODT_EDIT:
           $required=$required && !$userModerator;
           break;
      }
return $required;
}

function setPremoderates(&$posting,$original,$required=MODT_ALL)
{
$tmod=getModbitsByTopicId($posting->getParentId());
$tmod&=$required;
if(isModbitRequired($tmod,MODT_PREMODERATE,$original))
  {
  setDisabledByEntryId($posting->getId(),1);
  $posting->setDisabled(1);
  }
$modbits=MOD_NONE;
if(isModbitRequired($tmod,MODT_MODERATE,$original))
  $modbits|=MOD_MODERATE;
if(isModbitRequired($tmod,MODT_EDIT,$original))
  $modbits|=MOD_EDIT;
setModbitsByEntryId($posting->getId(),$modbits);
$posting->setModbits($modbits);
incContentVersions('postings');
}

function deleteShadowPosting($id)
{
sql("delete from entries
     where id=$id",
    __FUNCTION__,'delete_posting');
sql("delete from cross_entries
     where source_id=$id or peer_id=$id",
    __FUNCTION__,'delete_cross_postings');
incContentVersions('postings');
}

function getPostingShadowCount($origId)
{
$result=sql("select count(*)
             from entries
             where orig_id=$origId",
            __FUNCTION__);
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

function selectNewOrigPosting($origId)
{
$result=sql("select min(id)
             from entries
             where orig_id=$origId and id<>$origId",
            __FUNCTION__,'find');
if(mysql_num_rows($result)<=0)
  return;
$destId=mysql_result($result,0,0);
sql("update entries
     set up=$destId
     where up=$origId",
    __FUNCTION__,'update_up');
sql("update entries
     set parent_id=$destId
     where parent_id=$origId",
    __FUNCTION__,'update_parent_id');
sql("update entries
     set orig_id=$destId
     where orig_id=$origId",
    __FUNCTION__,'move');
$fields=origFields(SELECT_ALLPOSTING);
$result=sql("select $fields
             from entries
             where id=$origId",
            __FUNCTION__,'get_fields');
$row=mysql_num_rows($result)>0 ? mysql_fetch_assoc($result) : array();
sql(sqlUpdate('entries',
              $row,
              array('id' => $destId)),
    __FUNCTION__,'move_fields');
sql("update inner_images
     set entry_id=$destId
     where entry_id=$origId",
    __FUNCTION__,'inner_images');
updateCatalogs(trackById('entries',$origId).' ');
replaceTracks('entries',trackById('entries',$origId).' ',
              trackById('entries',$destId).' ');
incContentVersions('postings');
}

function deletePosting($id)
{
$posting=getPostingById($id);
if($id!=$posting->getOrigId())
  {
  deleteShadowPosting($id);
  return;
  }
if(getPostingShadowCount($id)>1)
  {
  selectNewOrigPosting($id);
  deleteShadowPosting($id);
  return;
  }
$up=$posting->getUpValue();
sql("update entries
     set up=$up
     where up=$id and entry<>".ENT_IMAGE." and entry<>".ENT_FORUM,
    __FUNCTION__,'update_up');
$result=sql("select id,small_image,small_image_format,
                    large_image,large_image_format
             from entries
             where parent_id=$id or up=$id and entry=".ENT_IMAGE,
            __FUNCTION__,'select_children');
sql("delete from entries
     where parent_id=$id or up=$id and entry=".ENT_IMAGE,
    __FUNCTION__,'delete_children');
sql("delete from inner_images
     where entry_id=$id",
    __FUNCTION__,'inner_images');
$result=sql("select id
             from entries
             where up=$id and entry=".ENT_FORUM,
            __FUNCTION__,'select_forum');
while($row=mysql_fetch_assoc($result))
     deleteForum($row['id']);
updateCatalogs($posting->getTrack().' ');
replaceTracks('entries',$posting->getTrack().' ',trackById('entries',$up).' ');
deleteShadowPosting($id);
}

function createPostingShadow($id)
{
global $userId,$realUserId;

$result=sql("select entry,up,parent_id,orig_id,grp,person_id,guest_login,
                    user_id,group_id,perms,disabled,lang,priority,index0,
                    index1,index2,set0,set0_index,set1,set1_index,vote,
                    vote_count,rating,sent,accessed,modbits,answers,
                    last_answer,last_answer_id,last_answer_user_id,
                    last_answer_guest_login
             from entries
             where id=$id",
            __FUNCTION__,'select');
if(mysql_num_rows($result)<=0)
  return;
$row=mysql_fetch_assoc($result);
$row['created']=sqlNow();
$row['modified']=sqlNow();
$row['creator_id']=$userId>0 ? $userId : $realUserId;
$row['modifier_id']=$row['creator_id'];
sql(sqlInsert('entries',
              $row),
    __FUNCTION__,'insert');
$shid=sql_insert_id();
createTrack('entries',$shid);
updateCatalogs(trackById('entries',$shid));
incContentVersions('postings');
}

function autoEnablePostings()
{
global $messageEnableTimeout;

$now=sqlNow();
$result=sql('select id
             from entries
             where entry='.ENT_POSTING.' and disabled<>0
                   and (modbits & '.MOD_MODERATE.")<>0
                   and modified+interval $messageEnableTimeout hour<'$now'",
            __FUNCTION__);
while($row=mysql_fetch_assoc($result))
     setDisabledByEntryId($row['id'],0);
}
?>
