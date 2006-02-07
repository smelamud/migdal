<?php
# @(#) $Id$

require_once('lib/alphabet.php');
require_once('lib/bug.php');
require_once('lib/cache.php');
require_once('lib/counters.php');
//require_once('lib/forums.php'); # FIXME
require_once('lib/grps.php');
require_once('lib/limitselect.php');
require_once('lib/entries.php');
require_once('lib/grpentry.php');
require_once('lib/mtext-shorten.php');
//require_once('lib/paragraphs.php');
require_once('lib/random.php');
require_once('lib/selectiterator.php');
require_once('lib/select.php');
require_once('lib/sort.php');
require_once('lib/sql.php');
//require_once('lib/stotext-images.php');
require_once('lib/text.php');
require_once('lib/topics.php');
require_once('lib/track.php');
require_once('lib/users.php');
require_once('lib/votes.php');
require_once('lib/uri.php');

class Posting
      extends GrpEntry
{
var $topic_ident;
var $topic_subject;
var $topic_body;
var $topic_body_xml;
var $topic_body_format;
var $counter_value0;
var $counter_value1;
var $co_ctr;

function Posting($row)
{
$this->entry=ENT_POSTING;
parent::GrpEntry($row);
}

function setup($vars)
{
if(!isset($vars['edittag']) || !$vars['edittag'])
  return;
// from Stotext FIXME
$this->body_format=TF_PLAIN;
$this->body=$vars['body'];
$this->body_xml=wikiToXML($this->body,$this->body_format,MTEXT_SHORT);
$this->large_body_format=$vars['large_body_format'];
if(!c_digit($this->large_body_format) || $this->large_body_format>TF_MAX)
  $this->large_body_format=TF_PLAIN;
$this->has_large_body=0;
if($vars["large_body"]!='')
  {
  $this->has_large_body=1;
  $this->large_body=$vars["large_body"];
  $this->large_body_xml=wikiToXML($this->large_body,$this->large_body_format,
                                  MTEXT_LONG);
  }
// from Message FIXME
$this->up=$vars['up'];
$this->subject=$vars['subject'];
$this->subject_sort=convertSort($this->subject);
$this->comment0=$vars['comment0'];
$this->comment0_xml=wikiToXML($this->comment0,$this->body_format,MTEXT_LINE);
$this->comment1=$vars['comment1'];
$this->comment1_xml=wikiToXML($this->comment1,$this->body_format,MTEXT_LINE);
$this->author=$vars['author'];
$this->author_xml=wikiToXML($this->author,$this->body_format,MTEXT_LINE);
$this->source=$vars['source'];
$this->source_xml=wikiToXML($this->source,$this->body_format,MTEXT_LINE);
$this->login=$vars['login'];
if($vars['user_name']!='')
  $this->login=$vars['user_name'];
$this->group_login=$vars['group_login'];
if($vars['group_name']!='')
  $this->group_login=$vars['group_name'];
$this->perm_string=$vars['perm_string'];
if($this->perm_string!='')
  $this->perms=permString($this->perm_string,strPerms($this->perms));
else
  if($vars['hidden'])
    $this->perms&=~0x1100;
  else
    $this->perms|=0x1100;
$this->lang=$vars['lang'];
$this->disabled=$vars['disabled'];
$this->url=$vars['url'];
$this->url_domain=getURLDomain($this->url);
// from Posting FIXME
$this->ident=$vars['ident']!='' ? $vars['ident'] : null;
$this->index1=$vars['index1'];
$this->index2=$vars['index2'];
array_push($list,'grp','personal_id','priority');
$this->parent_id=$vars['parent_id'];
}

// from Message FIXME
function isPermitted($right)
{
global $userModerator,$userId;

return $userModerator
       ||
       (!$this->isDisabled() || $this->getUserId()==$userId) &&
       perm($this->getUserId(),$this->getGroupId(),$this->getPerms(),$right);
}

function isShadow()
{
return $this->getId()!=$this->getOrigId();
}

function getTopicId()
{
return $this->getParentId();
}

function getTopicIdent()
{
return $this->topic_ident;
}

function getTopicSubject()
{
return $this->topic_subject;
}

function getTopicName()
{
return $this->getTopicSubject();
}

function getTopicBody()
{
return $this->topic_body;
}

function getTopicBodyXML()
{
return $this->topic_body_xml;
}

function getTopicBodyHTML()
{
return mtextToHTML($this->getTopicBodyXML(),$this->getTopicBodyFormat(),
                   $this->getTopicId());
}

function getTopicBodyNormal()
{
return shortenNote($this->topic_body_xml,65535,0,0);
}

function getTopicBodyFormat()
{
return $this->topic_body_format;
}

function getCounterValue0()
{
return $this->counter_value0;
}

function getCounterValue1()
{
return $this->counter_value1;
}

function getCTR()
{
return 1/$this->co_ctr;
}

function isMandatory($field)
{
$editor=$this->getGrpEditor();
for($editor as $item)
   if($item['ident']==$field)
     return $item['mandatory'];
return false;
}

}

// from Message FIXME
function postingsPermFilter($right,$prefix='')
{
global $userModerator,$userId;

if($userModerator)
  return '1';
$filter=permFilter($right,$prefix);
if($prefix!='' && substr($prefix,-1)!='.')
  $prefix.='.';
return "$filter and (${prefix}disabled=0".
       ($userId>0 ? " or ${prefix}user_id=$userId)" : ')');
}

// from Message FIXME
function postingExists($id)
{
$hide=postingsPermFilter(PERM_READ);
$result=sql("select id
	     from entries
	     where id=$id and $hide",
	    __FUNCTION__);
return mysql_num_rows($result)>0;
}

function postingListFields($fields=SELECT_GENERAL)
{
$Fields='entries.id as id,entries.ident as ident,entries.up as up,
         entries.track as track,entries.parent_id as parent_id,
	 entries.orig_id as orig_id,entries.grp as grp,
	 entries.person_id as person_id,entries.user_id as user_id,
	 entries.group_id as group_id,entries.perms as perms,
	 entries.disabled as disabled,entries.subject as subject,
	 entries.lang as lang,
	 entries.author as author,entries.author_xml as author_xml,
	 entries.source as source,entries.source_xml as source_xml,
	 entries.title as title,entries.title_xml as title_xml,
	 entries.comment0 as comment0,entries.comment0_xml as comment0_xml,
	 entries.comment1 as comment1,entries.comment1_xml as comment1_xml,
         entries.url as url,entries.url_domain as url_domain,
	 entries.url_check_success as url_check_success,entries.body as body,
	 entries.body_xml as body_xml,entries.has_large_body as has_large_body,
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
	 if(entries.answers!=0,entries.last_answer,entries.modified) as age,
	 entries.small_image as small_image,
	 entries.small_image_x as small_image_x,
	 entries.small_image_y as small_image_y,
	 entries.large_image as large_image,
	 entries.large_image_x as large_image_x,
	 entries.large_image_y as large_image_y,
	 entries.large_image_size as large_image_size,
	 entries.large_image_format as large_image_format,
	 entries.large_image_filename as large_image_filename,
	 users.login as login,users.gender as gender,users.email as email,
	 users.hide_email as hide_email,users.hidden as user_hidden';
if(($fields & SELECT_LARGE_BODY)!=0)
  $Fields.=',entries.large_body as large_body,
            entries.large_body_xml as large_body_xml';
if(($fields & SELECT_TOPICS)!=0)
  $Fields.=',topics.subject as topic_subject,topics.body as topic_body,
            topics.body_xml as topic_body_xml,
	    topics.body_format as topic_body_format,
	    topics.ident as topic_ident';
if(($fields & SELECT_CTR)!=0)
  $Fields.=',counter0.value as counter_value0,
	    counter1.value as counter_value1,
	    if(counter1.value is null or counter1.value=0,
	       1000000,counter0.value/counter1.value) as co_ctr';
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
	 entries.body_xml as body_xml,entries.has_large_body as has_large_body,
	 entries.large_body_format as large_body_format,
	 entries.large_body_filename as large_body_filename,
	 entries.small_image as small_image,
	 entries.small_image_x as small_image_x,
	 entries.small_image_y as small_image_y,
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

function postingListTables($fields=SELECT_GENERAL)
{
$Tables='entries
         left join users
	      on entries.user_id=users.id';
if(($fields & SELECT_TOPICS)!=0)
  $Tables.=' left join entries as topics
		  on entries.parent_id=topics.id';
if(($fields & SELECT_CTR)!=0)
  $Tables.=' left join counters as counter0
	          on entries.orig_id=counter0.entry_id and counter0.serial=1
		     and counter0.mode='.CMODE_EAR_HITS.'
             left join counters as counter1
	          on entries.orig_id=counter1.entry_id and counter1.serial=1
	 	     and counter1.mode='.CMODE_EAR_CLICKS;
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
			   $modbits=MOD_NONE,$hidden=-1,$disabled=-1)
{
$Filter='entries.entry='.ENT_POSTING;
$Filter.=' and '.postingsPermFilter(PERM_READ,'entries');
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
  $Filter.=" and unix_timestamp(entries.sent)>$later";
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
return $Filter;
}

class PostingListIterator
      extends LimitSelectIterator
{
var $fields;
var $where;

function PostingListIterator($grp,$topic_id=-1,$recursive=false,$limit=10,
                             $offset=0,$person_id=-1,$sort=SORT_SENT,
			     $withAnswers=GRP_NONE,$user=0,$index1=-1,$later=0,
			     $up=-1,$showShadows=true,$fields=SELECT_GENERAL,
			     $modbits=MOD_NONE,$hidden=-1,$disabled=-1)
{
if($sort==SORT_CTR)
  $fields|=SELECT_CTR;
$this->fields=$fields;

$Select=$showShadows ? postingListFields($this->fields)
		     : 'distinct entries.orig_id';
$From=postingListTables($this->fields);
$this->where=postingListFilter($grp,$topic_id,$recursive,$person_id,$sort,
                               $withAnswers,$user,$index1,$later,$up,$fields,
			       $modbits,$hidden,$disabled);
$Order=getOrderBy($sort,
       array(SORT_SENT       => 'entries.sent desc',
             SORT_NAME       => 'entries.subject_sort',
             SORT_ACTIVITY   => 'age desc',
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
$this->LimitSelectIterator('Posting',
			   "select $Select
			    from $From
			    where {$this->where}
			    $Order",
			   $limit,$offset,
			   "select count(*)
			    from $From
			    where {$this->where}");
}

function create($row)
{
if($row['id']<=0 && $row['orig_id']>0)
  {
  $Select=postingListFields($this->fields);
  $From=postingListTables($this->fields);
  $Where="{$this->where} and entries.orig_id={$row['orig_id']}";
  $result=sql("select $Select
               from $From
	       where $Where
	       order by entries.id",
	      __FUNCTION__,'shadow');
  $shadow=mysql_num_rows($result)>0 ? mysql_fetch_assoc($result) : array();
  $row=array_merge($row,$shadow);
  }
if($row['id']!=$row['orig_id'])
  {
  $Select=origFields($this->fields);
  $From=origTables($this->fields);
  $Where="entries.id={$row['orig_id']}";
  $result=sql("select $Select
               from $From
	       where $Where",
	      __FUNCTION__,'original');
  $orig=mysql_num_rows($result)>0 ? mysql_fetch_assoc($result) : array();
  $row=array_merge($row,$orig);
  }
if($row['parent_id']>0)
  {
  if($row['topic_ident']!='')
    setCachedValue('ident','entries',$row['topic_ident'],$row['parent_id']);
  setCachedValue('track','entries',$row['parent_id'],$row['topic_track']);
  }
if($row['id']>0)
  {
  if($row['ident']!='')
    setCachedValue('ident','entries',$row['ident'],$row['id']);
  setCachedValue('track','entries',$row['id'],$row['track']);
  }
return parent::create($row);
}

}

class PostingUsersIterator
      extends SelectIterator
{

function PostingUsersIterator($grp=GRP_ALL,$topic_id=-1,$recursive=false)
{
$hide=messagesPermFilter(PERM_READ,'messages');
$grpFilter=grpFilter($grp);
$topicFilter=($topic_id<0 || $recursive && $topic_id==0) ? ''
             : ' and topics.'.subtree('topics',$topic_id,$recursive);
$this->SelectIterator(
       'User',
       "select distinct users.id as id,login,gender,email,hide_email,rebe,
                        users.name as name,jewish_name,surname,
			max(sent) as last_message
        from users
	     left join messages
	          on messages.sender_id=users.id
	     left join postings
	          on postings.message_id=messages.id
	     left join topics
	          on postings.topic_id=topics.id
	where $hide and $grpFilter $topicFilter
	group by users.id
	order by surname,jewish_name,name");
}

}

/*class PostingParagraphIterator
      extends ParagraphIterator
{
var $images;

function PostingParagraphIterator($posting,$noteBase=1)
{
$this->ParagraphIterator($posting->getLargeFormat(),$posting->getLargeBody(),
                         $posting->getMessageId(),$noteBase);
$this->loadImages($posting);
}

function loadImages($posting)
{
$this->images=array();
$sid=$posting->getStotextId();
settype($sid,'integer');
$result=sql("select stotext_id,par,image_id,placement,
		    has_large as has_large_image,title,format
	     from stotext_images
		  left join images
		       on images.id=stotext_images.image_id
	     where stotext_id=$sid",
	    get_method($this,'loadImages'));
while($row=mysql_fetch_assoc($result))
     {
     $image=new StotextImage($row);
     $this->images[$image->getPar()]=$image;
     }
}

function next()
{
$paragraph=ParagraphIterator::next();
if($paragraph)
  $paragraph->setImage($this->images[$paragraph->getNumber()]);
return $paragraph;
}

}*/

class ArticleCoversIterator
      extends SelectIterator
{

function ArticleCoversIterator($articleGrp,$coverGrp)
{
$hideMessages=messagesPermFilter(PERM_READ,'messages');
$hideCovers=messagesPermFilter(PERM_READ,'cover_messages');
$joinGrpFilter=grpFilter($coverGrp,'grp','covers');
$articleGrpFilter=grpFilter($articleGrp,'grp','postings');
$coverGrpFilter=grpFilter($coverGrp,'grp','postings');
$this->SelectIterator(
       'Posting',
       "select distinct postings.index1 as index1,covers.index2 as index2,
               cover_messages.source as source
        from postings
             left join postings as covers
	          on postings.index1=covers.index1 and
		     $joinGrpFilter
	     left join messages
	          on postings.message_id=messages.id
	     left join messages as cover_messages
	          on covers.message_id=cover_messages.id
	where ($articleGrpFilter or $coverGrpFilter) and
	      $hideMessages and (covers.id is null or $hideCovers)
	order by postings.index1 desc");
}

function create($row)
{
return newGrpPosting(GRP_TIMES_COVER,$row);
}

}

class PostingAlphabetIterator
      extends AlphabetIterator
{

function PostingAlphabetIterator($sort=SORT_URL_DOMAIN,$topic_id=-1,
                                 $recursive=false,$grp=GRP_ALL,
				 $showShadows=false)
{
$hide=messagesPermFilter(PERM_READ,'messages');
$fields=array(SORT_NAME       => 'subject',
	      SORT_URL_DOMAIN => 'url_domain');
$field=@$fields[$sort]!='' ? $fields[$sort] : 'url';
$order=getOrderBy($sort,
       array(SORT_NAME       => 'subject',
	     SORT_URL_DOMAIN => 'url_domain,url'));
$topicFilter=$topic_id>=0
             ? 'and topics.'.subtree('topics',$topic_id,$recursive) : '';
$grpFilter='and '.grpFilter($grp);
$shadowFilter=!$showShadows ? 'and shadow=0' : '';
$this->AlphabetIterator(
        "select left($field,1) as letter,count(*) as count
         from messages
	 left join postings
	      on postings.message_id=messages.id
	 left join topics
	      on topics.id=postings.topic_id
         where $hide $topicFilter $grpFilter $shadowFilter
	 group by messages.id
	 $order",true);
}

}

/*function storePosting(&$posting)
{
function getWorldPostingVars()
{
return array('message_id','topic_id','grp','personal_id','index1','index2');
}

function getAdminPostingVars()
{
return array('ident','priority');
}

function getJencodedPostingVars()
{
return array('message_id' => 'messages','topic_id' => 'topics',
             'personal_id' => 'users');
}

global $userModerator;

$result=Message::store('message_id');
if(!$result)
  return $result;
$normal=$this->getNormalPosting($userModerator);
if($this->id)
  {
  $result=sql(makeUpdate('postings',
                         $normal,
			 array('id' => $this->id)),
	      get_method($this,'store'),'update');
  journal(makeUpdate('postings',
                     jencodeVars($normal,$this->getJencodedPostingVars()),
		     array('id' => journalVar('postings',$this->id))));
  }
else
  {
  $result=sql(makeInsert('postings',
                         $normal),
	      get_method($this,'store'),'insert');
  $this->id=sql_insert_id();
  journal(makeInsert('postings',
                     jencodeVars($normal,$this->getJencodedPostingVars())),
	  'postings',$this->id);
  }
return $result;
}*/

function getRootPosting($grp,$topic_id,$up)
{
global $rootPostingPerms;

if($up>0)
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
			 'group_id'  => $group_id,
			 'perms'     => $perms));
}

function getPostingById($id=-1,$grp=GRP_ALL,$index1=-1,$topic_id=-1,
                        $fields=SELECT_GENERAL,$up=-1)
{
$Select=postingListFields($fields);
$From=postingListTables($fields);
if($id>0)
  $Where="entries.id=$id";
else
  {
  $Where='entries.entry='.ENT_POSTING;
  $Where.=' and '.postingsPermFilter(PERM_READ,'entries');
  $Where.=' and '.postingListGrpFilter($grp);
  $Where.=' and '.postingListTopicFilter($topic_id);
  if($index1>=0)
    $Where.=" and entries.index1=$index1";
  }
$result=sql("select $Select
	     from $From
	     where $Where",
	    __FUNCTION__,'shadow');
if(mysql_num_rows($result)>0)
  {
  $row=mysql_fetch_assoc($result);
  if($row['id']!=$row['orig_id'])
    {
    $Select=origFields($fields);
    $From=origTables($fields);
    $Where="entries.id={$row['orig_id']}";
    $result=sql("select $Select
		 from $From
		 where $Where",
		__FUNCTION__,'original');
    $orig=mysql_num_rows($result)>0 ? mysql_fetch_assoc($result) : array();
    $row=array_merge($row,$orig);
    }
  return new Posting($row);
  }
else
  return getRootPosting($grp,$topic_id,$up);
}

function getMaxIndexOfPosting($indexN,$grp,$topic_id=-1,$recursive=false)
{
$hide=messagesPermFilter(PERM_READ,'messages');
$grpFilter=grpFilter($grp);
$topicFilter=($topic_id<0 || $recursive && $topic_id==0)
             ? '' : ' and topics.'.subtree('topics',$topic_id,$recursive);
$result=sql("select max(postings.index$indexN)
	     from postings
		  left join messages
		       on postings.message_id=messages.id
		  left join topics
		       on postings.topic_id=topics.id
	     where $hide and $grpFilter $topicFilter",
	    'getMaxIndexOfPosting');
return mysql_num_rows($result)>0 ? (int)mysql_result($result,0,0) : 0;
}

function getVoteInfoByPostingId($id,$grp=GRP_ALL)
{
$result=sql("select id,vote,vote_count
	     from postings
	     where id=$id",
	    'getVoteInfoByPostingId');
return mysql_num_rows($result)>0 ? newPosting(mysql_fetch_assoc($result))
                                 : newGrpPosting($grp);
}

function getPostingIdByMessageId($id)
{
$result=sql("select id
	     from postings
	     where message_id=$id",
	    'getPostingIdByMessageId');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

function getSibling($up,$index0,$next=true)
{
$filter=$next ? "index0>$index0" : "index0<$index0";
$order=$next ? 'asc' : 'desc';
$result=sql("select postings.id
	     from postings
		  left join messages
		       on postings.message_id=messages.id
	     where up=$up and $filter
	     order by index0 $order
	     limit 1",
	    'getSibling');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

function getSiblingIndex0($up,$index0,$next=true)
{
$filter=$next ? "index0>$index0" : "index0<$index0";
$order=$next ? 'asc' : 'desc';
$result=sql("select index0
	     from postings
		  left join messages
		       on postings.message_id=messages.id
	     where up=$up and $filter
	     order by index0 $order
	     limit 1",
	    'getSiblingIndex0');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : -1;
}

function getSiblingIssue($grp,$topic_id,$index1,$next=true)
{
$grpFilter=grpFilter($grp);
$issueFilter=$next ? "and index1>$index1" : "and index1<$index1";
$order=$next ? 'asc' : 'desc';
$topicFilter=$topic_id>=0 ? "and topic_id=$topic_id" : '';
$result=sql("select postings.id
	     from postings
		  left join messages
		       on postings.message_id=messages.id
	     where $grpFilter $topicFilter $issueFilter
	     order by index1 $order
	     limit 1",
	    'getSiblingIssue');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

function getSiblingIndex1($grp,$topic_id,$index1,$next=true)
{
$grpFilter=grpFilter($grp);
$issueFilter=$next ? "and index1>$index1" : "and index1<$index1";
$order=$next ? 'asc' : 'desc';
$topicFilter=$topic_id>=0 ? "and topic_id=$topic_id" : '';
$result=sql("select index1
	     from postings
		  left join messages
		       on postings.message_id=messages.id
	     where $grpFilter $topicFilter $issueFilter
	     order by index1 $order
	     limit 1",
	    'getSiblingIndex1');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

function getPostingDomainCount($topic_id=-1,$recursive=false,$grp=GRP_ALL)
{
$hide=messagesPermFilter(PERM_READ,'messages');
$topicFilter=$topic_id>=0
             ? 'and topics.'.subtree('topics',$topic_id,$recursive) : '';
$grpFilter='and '.grpFilter($grp);
$result=sql("select count(distinct url_domain)
	     from messages
	     left join postings
		  on postings.message_id=messages.id
	     left join topics
		  on topics.id=postings.topic_id
	     where $hide and shadow=0 $topicFilter $grpFilter",
	    'getPostingDomainCount');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}
?>
