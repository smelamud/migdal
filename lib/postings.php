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

class Posting
      extends GrpEntry
{
var $counter_value0;
var $counter_value1;
var $co_ctr;

function Posting($row)
{
$this->entry=ENT_POSTING;
$this->body_format=TF_MAIL;
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
$this->large_body='';
$this->large_body_xml='';
if($vars['large_body']!='')
  {
  $this->has_large_body=1;
  $this->large_body=$vars['large_body'];
  $this->large_body_xml=wikiToXML($this->large_body,$this->large_body_format,
                                  MTEXT_LONG);
  }
if($vars['large_body_filename']!='')
  $this->large_body_filename=$vars['large_body_filename'];
$this->small_image=$vars['small_image'];
$this->small_image_x=$vars['small_image_x'];
$this->small_image_y=$vars['small_image_y'];
$this->large_image=$vars['large_image'];
$this->large_image_x=$vars['large_image_x'];
$this->large_image_y=$vars['large_image_y'];
$this->large_image_size=$vars['large_image_size'];
$this->large_image_format=$vars['large_image_format'];
$this->large_image_filename=$vars['large_image_filename'];
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
$this->title=$vars['title'];
$this->title_xml=wikiToXML($this->title,$this->body_format,MTEXT_LINE);
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
$this->parent_id=$vars['parent_id'];
$this->grp=$vars['grp'];
$this->person_id=$vars['person_id'];
$this->priority=$vars['priority'];
if($this->up<=0)
  $this->up=$this->parent_id;
else
  if(getTypeByEntryId($this->up)==ENT_POSTING)
    $this->parent_id=getParentIdByEntryId($this->up);
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
foreach($editor as $item)
       if($item['ident']==$field)
	 return $item['mandatory'];
return false;
}

function getHeading()
{
$heading='';
if(method_exists($this,'getGrpHeading'))
  $heading=$this->getGrpHeading();
if($heading=='' && method_exists($this,'getSubject'))
  $heading=$this->getSubject();
if($heading=='' && method_exists($this,'getBodyTiny'))
  $heading=$this->getBodyTiny();
if($heading=='')
  $heading='Без названия';
return $heading;
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
	     where id=$id and entry=".ENT_POSTING." and $hide",
	    __FUNCTION__);
return mysql_num_rows($result)>0;
}

function postingListFields($fields=SELECT_GENERAL)
{
$Fields='entries.id as id,entries.ident as ident,entries.up as up,
         entries.track as track,entries.catalog as catalog,
	 entries.parent_id as parent_id,entries.orig_id as orig_id,
	 entries.grp as grp,entries.person_id as person_id,
	 entries.user_id as user_id,entries.group_id as group_id,
	 entries.perms as perms,entries.disabled as disabled,
	 entries.subject as subject,entries.lang as lang,
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
			   $withIdent=false)
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
if($prefix!='')
  {
  $prefixFilters=array(SORT_NAME       => " and entries.subject like '$prefix%'",
                       SORT_URL_DOMAIN => " and entries.url_domain like '$prefix%'
		                            and entries.url_domain<>''");
  $Filter.=@$prefixFilters[$sort]!='' ? $prefixFilters[$sort] : '';
  }
if($withIdent)
  $Filter.=" and entries.ident<>''";
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
			     $modbits=MOD_NONE,$hidden=-1,$disabled=-1,
			     $prefix='',$withIdent=false)
{
if($sort==SORT_CTR)
  $fields|=SELECT_CTR;
$this->fields=$fields;

$Select=$showShadows ? postingListFields($this->fields)
		     : 'distinct entries.orig_id';
$SelectCount=$showShadows ? 'count(*)'
		          : 'count(distinct entries.orig_id)';
$From=postingListTables($this->fields,$sort);
$this->where=postingListFilter($grp,$topic_id,$recursive,$person_id,$sort,
                               $withAnswers,$user,$index1,$later,$up,$fields,
			       $modbits,$hidden,$disabled,$prefix,$withIdent);
$Order=getOrderBy($sort,
       array(SORT_SENT       => 'entries.sent desc',
             SORT_NAME       => 'entries.subject_sort',
             SORT_ACTIVITY   => 'if(entries.answers!=0,entries.last_answer,entries.modified) desc',
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
			   "select $SelectCount
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

// remake
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

// remake
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

function PostingAlphabetIterator($limit=0,$sort=SORT_URL_DOMAIN,$topic_id=-1,
                                 $recursive=false,$grp=GRP_ALL,
				 $showShadows=false)
{
$hide='and '.postingsPermFilter(PERM_READ);
$fields=array(SORT_NAME       => 'subject',
	      SORT_URL_DOMAIN => 'url_domain');
$field=@$fields[$sort]!='' ? $fields[$sort] : 'url';
$prefixFilters=array(SORT_NAME       => "and subject like '@prefix@%'",
	             SORT_URL_DOMAIN => "and url_domain like '@prefix@%'
		                         and url_domain<>''");
$prefixFilter=@$prefixFilters[$sort]!='' ? $prefixFilters[$sort]
                                         : " and url like '@prefix@%'";
$order=getOrderBy($sort,
                  array(SORT_NAME       => 'subject_sort',
	                SORT_URL_DOMAIN => 'url_domain,url'));
$topicFilter=$topic_id>=0 ? 'and '.subtree('entries',$topic_id,$recursive) : '';
$grpFilter='and '.grpFilter($grp);
$shadowFilter=!$showShadows ? 'and id=orig_id' : '';
parent::AlphabetIterator(
        "select left($field,@len@) as letter,1 as count
         from entries
         where entry=".ENT_POSTING." $hide $topicFilter $grpFilter
	       $shadowFilter $prefixFilter
	 $order",true);
}

}

define('SPF_ORIGINAL',1);
define('SPF_DUPLICATE',2);
define('SPF_SHADOW',4);
define('SPF_ALL',SPF_ORIGINAL|SPF_DUPLICATE|SPF_SHADOW);

function storePostingFields(&$posting,$fields)
{
global $userModerator;

$vars=array('entry' => $posting->entry,
            'modified' => sqlNow());
if(($fields & SPF_ORIGINAL)!=0)
  $vars=array_merge($vars,
                    array('subject' => $posting->subject,
		          'author' => $posting->author,
		          'author_xml' => $posting->author_xml,
		          'source' => $posting->source,
		          'source_xml' => $posting->source_xml,
		          'title' => $posting->title,
		          'title_xml' => $posting->title_xml,
		          'comment0' => $posting->comment0,
		          'comment0_xml' => $posting->comment0_xml,
		          'comment1' => $posting->comment1,
		          'comment1_xml' => $posting->comment1_xml,
			  'url' => $posting->url,
			  'url_domain' => $posting->url_domain,
			  'body' => $posting->body,
			  'body_xml' => $posting->body_xml,
			  'body_format' => $posting->body_format,
			  'has_large_body' => $posting->has_large_body,
			  'large_body' => $posting->large_body,
			  'large_body_xml' => $posting->large_body_xml,
			  'large_body_format' => $posting->large_body_format,
			  'large_body_filename' => $posting->large_body_filename,
			  'small_image' => $posting->small_image,
			  'small_image_x' => $posting->small_image_x,
			  'small_image_y' => $posting->small_image_y,
			  'large_image' => $posting->large_image,
			  'large_image_x' => $posting->large_image_x,
			  'large_image_y' => $posting->large_image_y,
			  'large_image_size' => $posting->large_image_size,
			  'large_image_format' => $posting->large_image_format,
			  'large_image_filename' => $posting->large_image_filename));
if(($fields & SPF_DUPLICATE)!=0)
  {
  $vars=array_merge($vars,
                    array('person_id' => $posting->person_id,
		          'user_id' => $posting->user_id,
			  'group_id' => $posting->group_id,
			  'perms' => $posting->perms,
			  'subject_sort' => $posting->subject_sort,
			  'lang' => $posting->lang,
			  'index1' => $posting->index1,
			  'index2' => $posting->index2));
  if($userModerator)
    $vars=array_merge($vars,
		      array('disabled' => $posting->disabled,
		            'priority' => $posting->priority));
  }
if(($fields & SPF_SHADOW)!=0)
  {
  $vars=array_merge($vars,
                    array('up' => $posting->up,
		          'track' => $posting->track,
		          'catalog' => $posting->catalog,
			  'parent_id' => $posting->parent_id,
			  'grp' => $posting->grp));
  if($userModerator)
    $vars=array_merge($vars,
		      array('ident' => $posting->ident));
  }
return $vars;
}

function storePosting(&$posting)
{
$jencoded=array('subject' => '','author' => '','author_xml' => '',
                'source' => '','source_xml' => '','title' => '',
		'title_xml' => '','comment0' => '','comment0_xml' => '',
		'comment1' => '','comment1_xml' => '','url' => '',
		'url_domain' => '','body' => '','body_xml' => '',
		'large_body' => '','large_body_xml' => '',
		'large_body_filename' => '','small_image' => 'images',
		'large_image' => 'images','large_image_filename' => '',
		'person_id' => 'users','user_id' => 'users',
		'group_id' => 'users','subject_sort' => '','up' => 'entries',
		'parent_id' => 'entries');
if($posting->id)
  {
  $vars=storePostingFields($posting,SPF_SHADOW);
  $result=sql(makeUpdate('entries',
                         $vars,
			 array('id' => $posting->id)),
	      __FUNCTION__,'update_shadow');
  journal(makeUpdate('entries',
                     jencodeVars($vars,$jencoded),
		     array('id' => journalVar('entries',$posting->id))));
  $vars=storePostingFields($posting,SPF_DUPLICATE);
  $result=sql(makeUpdate('entries',
                         $vars,
			 array('orig_id' => $posting->orig_id)),
	      __FUNCTION__,'update_duplicate');
  journal(makeUpdate('entries',
                     jencodeVars($vars,$jencoded),
		     array('orig_id' => journalVar('entries',$posting->orig_id))));
  $vars=storePostingFields($posting,SPF_ORIGINAL);
  $result=sql(makeUpdate('entries',
                         $vars,
			 array('id' => $posting->orig_id)),
	      __FUNCTION__,'update_original');
  journal(makeUpdate('entries',
                     jencodeVars($vars,$jencoded),
		     array('id' => journalVar('entries',$posting->orig_id))));
  }
else
  {
  $vars=storePostingFields($posting,SPF_ALL);
  $vars['sent']=sqlNow();
  $vars['created']=sqlNow();
  $result=sql(makeInsert('entries',
                         $vars),
	      __FUNCTION__,'insert');
  $posting->id=sql_insert_id();
  journal(makeInsert('entries',
                     jencodeVars($vars,$jencoded)),
	  'entries',$posting->id);

  sql("update entries
       set orig_id=id
       where id={$posting->id}",
      __FUNCTION__,'orig_id');
  journal('update entries
	   set orig_id=id
	   where id='.journalVar('entries',$posting->id));
  }
return $result;
}

function getRootPosting($grp,$topic_id,$up)
{
global $userId,$realUserId,$rootPostingPerms;

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
			 'user_id'   => $userId>0 ? $userId : $realUserId,
			 'group_id'  => $group_id,
			 'perms'     => $perms));
}

function getPostingById($id=-1,$grp=GRP_ALL,$topic_id=-1,$fields=SELECT_GENERAL,
                        $up=-1)
{
$Select=postingListFields($fields);
$From=postingListTables($fields);
if($id=='' || $id<0)
  return getRootPosting($grp,$topic_id,$up);
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
  return new Posting($row);
  }
else
  return getRootPosting($grp,$topic_id,$up);
}

function getPostingId($grp=GRP_ALL,$index1=-1,$topic_id=-1)
{
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

// remake
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
$result=sql("select id,vote,vote_count,rating
	     from entries
	     where id=$id",
	    __FUNCTION__);
return mysql_num_rows($result)>0 ? new Posting(mysql_fetch_assoc($result))
                                 : new Posting(array('grp' => $grp));
}

// remake
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
$result=sql("select id
	     from entries
	     where up=$up and $filter
	     order by index0 $order
	     limit 1",
	    __FUNCTION__);
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

// remake
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
$grpFilter=postingListGrpFilter($grp);
$topicFilter=postingListTopicFilter($topic_id);
$issueFilter=$next ? "and index1>$index1" : "and index1<$index1";
$order=$next ? 'asc' : 'desc';
$result=sql("select id
	     from entries
	     where entry=".ENT_POSTING." and $grpFilter and $topicFilter
	           $issueFilter
	     order by index1 $order
	     limit 1",
	    __FUNCTION__);
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

// remake
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

function setPremoderates($posting,$original,$required=MODT_ALL)
{
$tmod=getModbitsByTopicId($posting->getParentId());
$tmod&=$required;
if(isModbitRequired($tmod,MODT_PREMODERATE,$original))
  setDisabledByEntryId($posting->getId(),1);
$modbits=MOD_NONE;
if(isModbitRequired($tmod,MODT_MODERATE,$original))
  $modbits|=MOD_MODERATE;
if(isModbitRequired($tmod,MODT_EDIT,$original))
  $modbits|=MOD_EDIT;
setModbitsByEntryId($posting->getId(),$modbits);
}
?>
