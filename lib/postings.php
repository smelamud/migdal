<?php
# @(#) $Id$

require_once('lib/alphabet.php');
require_once('lib/bug.php');
require_once('lib/cache.php');
//require_once('lib/counters.php');
//require_once('lib/forums.php');
require_once('lib/grps.php');
require_once('lib/limitselect.php');
require_once('lib/entries.php');
require_once('lib/grpentry.php');
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
$this->parent_id=$vars['parent_id']);
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

class PostingListIterator
      extends LimitSelectIterator
{
var $topicFilter;
var $answersRequired;

function PostingListIterator($grp,$topic_id=-1,$recursive=false,$limit=10,
                             $offset=0,$personal=0,$sort=SORT_SENT,
			     $withAnswers=GRP_NONE,$user=0,$index1=-1,$later=0,
			     $subdomain=-1,$up=-1,$showShadows=true,
			     $fields=SELECT_GENERAL,$modbits=MOD_NONE,
			     $hidden=-1,$disabled=-1)
{
global $userId;

$this->topicFilter='';
$this->addTopicFilter($topic_id,$recursive);
if($withAnswers)
  $fields|=SELECT_ANSWERS;
$sortByAnswers=$sort==SORT_ACTIVITY;
$this->answersRequired=($fields & SELECT_ANSWERS)!=0 && !$sortByAnswers &&
                        $userId>0;
/* Select */
$imageFields=($fields & SELECT_IMAGES)!=0 ?
             "images.image_set as image_set,images.id as image_id,
	      images.has_large as has_large_image,images.title as title,
	      if(images.has_large,length(images.large),
				  length(images.small)) as image_size,
	      if(images.has_large,images.large_x,images.small_x) as image_x,
	      if(images.has_large,images.large_y,images.small_y) as image_y," :
	     "stotexts.image_set as image_set,";
$topicFields=($fields & SELECT_TOPICS)!=0 ?
	     "topics.id as topic_id,topics.name as topic_name,
	      topictexts.body as topic_description,
	      topics.ident as topic_ident,topics.track as topic_track," :
	     "postings.topic_id as topic_id,";
$answersFields=$sortByAnswers && $userId>0 ?
	     "count(forummesgs.id) as answer_count,
	      max(forummesgs.sent) as last_answer,
	      ifnull(max(forummesgs.sent),messages.sent) as age," :
            (($fields & SELECT_ANSWERS)!=0 ?
	     "messages.answers as answer_count,
	      messages.hidden_answers as hidden_answers,
	      messages.last_answer as last_answer,
	      if(last_answer,last_answer,messages.sent) as age," :
	     "");
$countersFields=$sort==SORT_CTR ?
	     "counter0.value as counter_value0,
	      counter1.value as counter_value1,
	      if(counter1.value is null or counter1.value=0,
	         1000000,counter0.value/counter1.value) as co_ctr," :
	     "";

$Select="postings.id as id,postings.ident as ident,
         messages.track as track,postings.message_id as message_id,
         messages.stotext_id as stotext_id,stotexts.body as body,
	 messages.lang as lang,messages.subject as subject,
	 messages.author as author,messages.source as source,
	 messages.comment0 as comment0,messages.comment1 as comment1,grp,
	 messages.sent as sent,topic_id,messages.url as url,
	 messages.url_domain as url_domain,messages.sender_id as sender_id,
	 messages.group_id as group_id,messages.perms as perms,
	 if((messages.perms & 0x1100)=0,1,0) as hidden,
	 messages.disabled as disabled,messages.modbits as modbits,
	 users.hidden as sender_hidden,
	 postings.index0 as index0,postings.index1 as index1,
	 postings.index2 as index2,subdomain,shadow,priority,
	 $imageFields
	 $topicFields
	 login,gender,email,hide_email,rebe,
	 vote,vote_count,
	 $answersFields
	 $countersFields
	 if(messages.url_check_success=0,0,
	    unix_timestamp()-unix_timestamp(messages.url_check_success))
	                                            as url_fail_time";
/* From */
$imageTables=($fields & SELECT_IMAGES)!=0 ?
	     "left join images
		   on stotexts.image_set=images.image_set and
		      images.image_set<>0" :
	     '';
$topicTables=($fields & SELECT_TOPICS)!=0 ?
	     "left join topics
		   on postings.topic_id=topics.id
	      left join stotexts as topictexts
		   on topictexts.id=topics.stotext_id" :
	     '';
$hideAnswers=messagesPermFilter(PERM_READ,'forummesgs');
$answersTables=($fields & SELECT_ANSWERS)!=0 && $sortByAnswers && $userId>0 ?
	     "left join forums
		   on messages.id=forums.parent_id
	      left join messages as forummesgs
		   on forums.message_id=forummesgs.id and $hideAnswers" :
	     '';
$countersTables=$sort==SORT_CTR ?
             "left join counters as counter0
	           on messages.id=counter0.message_id and counter0.serial=1
		      and counter0.mode=".CMODE_EAR_HITS."
              left join counters as counter1
	           on messages.id=counter1.message_id and counter1.serial=1
		      and counter1.mode=".CMODE_EAR_CLICKS :
	     '';

$From="postings
       left join messages
	    on postings.message_id=messages.id
       left join stotexts
	    on stotexts.id=messages.stotext_id
       $imageTables
       $topicTables
       left join users
	    on messages.sender_id=users.id
       $answersTables
       $countersTables";
/* Where */
$hideMessages=messagesPermFilter(PERM_READ,'messages');
$grpFilter=grpFilter($grp);
$userFilter=$user<=0 ? '' : " and messages.sender_id=$user ";
if($withAnswers!=GRP_NONE)
  if($sortByAnswers && $userId>0)
    {
    $countAnswerFilter=' and forummesgs.id is not null';
    $selectAnswerFilter='';
    }
  else
    {
    $answerFilter=grpFilter($withAnswers);
    $countAnswerFilter=" and (not $answerFilter or messages.answers<>0)";
    $selectAnswerFilter=$countAnswerFilter;
    }
else
  {
  $countAnswerFilter='';
  $selectAnswerFilter='';
  }
$index1Filter=$index1>=0
              ? ($sort==SORT_INDEX1  ? "and postings.index1>=$index1" :
	        ($sort==SORT_RINDEX1 ? "and postings.index1<=$index1" :
		                       "and postings.index1=$index1"))
	      : '';
$sentFilter=$later>0 ? "and unix_timestamp(messages.sent)>$later" : '';
$subdomainFilter=$subdomain>=0 ? "and subdomain=$subdomain" : '';
$childFilter=$up>=0 ? "and messages.up=$up" : '';
$shadowFilter=!$showShadows ? 'and shadow=0' : '';
$modbitsFilter=$modbits>0 ? "and (messages.modbits & $modbits)!=0" : '';
$hiddenFilter=$hidden>0 ? "and (messages.perms & 0x1100)=0" :
             ($hidden=0 ? "and (messages.perms & 0x1100)<>0" : '');
$disabledFilter=$disabled>0 ? "and messages.disabled<>0" :
               ($disabled=0 ? "and messages.disabled=0" : '');

$Where="$hideMessages and personal_id=$personal and $grpFilter @topic@
	$userFilter $selectAnswerFilter $index1Filter $sentFilter
	$subdomainFilter $childFilter $shadowFilter $modbitsFilter
	$hiddenFilter $disabledFilter";
/* Group by */
$GroupBy=($fields & SELECT_ANSWERS)!=0 && $sortByAnswers && $userId>0
         ? 'group by postings.id' : '';
/* Having */
if($withAnswers!=GRP_NONE && $sortByAnswers && $userId>0)
  {
  $answerFilter=grpFilter($withAnswers);
  $havingFilter="having not $answerFilter or count(forummesgs.id)<>0";
  }
else
  $havingFilter='';

$Having=$havingFilter;
/* Order */
$Order=getOrderBy($sort,
       array(SORT_SENT       => 'sent desc',
             SORT_NAME       => 'subject',
             SORT_ACTIVITY   => 'age desc',
	     SORT_CTR        => 'hidden asc,co_ctr asc,counter_value0 asc',
	     SORT_INDEX0     => 'postings.index0',
	     SORT_INDEX1     => 'postings.index1',
	     SORT_RINDEX1    => 'postings.index1 desc',
	     SORT_RATING     => getRatingSQL('vote','vote_count').' desc,'.
	                        'vote_count desc,sent desc',
	     SORT_URL_DOMAIN => 'url_domain,url',
	     SORT_TOPIC_INDEX0_INDEX0
	                     => 'topics.index0,postings.index0',
             SORT_RSENT      => 'sent asc'));
/* Query */
$this->LimitSelectIterator(
       'Message',
       "select $Select
	from $From
	where $Where
	$GroupBy
	$Having
	$Order",$limit,$offset,
       "select count(distinct postings.id)
	from postings
	     left join messages
	          on postings.message_id=messages.id
	     left join topics
		  on postings.topic_id=topics.id
	     $answersTables
	where $hideMessages and personal_id=$personal and $grpFilter
	      $countAnswerFilter @topic@ $userFilter $index1Filter $sentFilter
	      $subdomainFilter $childFilter $shadowFilter $modbitsFilter
	      $hiddenFilter $disabledFilter");
}

function addTopicFilter($topic_id,$recursive=false)
{
if(!is_array($topic_id))
  {
  if($topic_id<0 || $recursive && $topic_id==0)
    return;
  if($this->topicFilter!='')
    $this->topicFilter.=' or ';
  $this->topicFilter.='topics.'.subtree('topics',$topic_id,$recursive);
  }
else
  foreach($topic_id as $id => $rec)
	 {
	 if($this->topicFilter!='')
	   $this->topicFilter.=' or ';
	 $this->topicFilter.='topics.'.subtree('topics',$id,$rec);
	 }
}

function getTopicFilterCondition()
{
return $this->topicFilter!='' ? " and ({$this->topicFilter})" : '';
}

function select()
{
$this->setQuery(str_replace('@topic@',$this->getTopicFilterCondition(),
                                      $this->getQuery()));
LimitSelectIterator::select();
}

function countSelect()
{
$this->setCountQuery(str_replace('@topic@',$this->getTopicFilterCondition(),
                                           $this->getCountQuery()));
LimitSelectIterator::countSelect();
}

function create($row)
{
if($row['topic_id']>0)
  {
  if($row['topic_ident']!='')
    setCachedValue('ident','topics',$row['topic_ident'],$row['topic_id']);
  setCachedValue('track','topics',$row['topic_id'],$row['topic_track']);
  }
if($row['id']>0)
  {
  if($row['ident']!='')
    setCachedValue('ident','postings',$row['ident'],$row['id']);
  setCachedValue('track','postings',$row['id'],$row['track']);
  }
if($this->answersRequired && $row['hidden_answers']>0)
  {
  $info=getForumAnswersInfoByMessageId($row['message_id']);
  $row=array_merge($row,$info);
  }
return newPosting($row);
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

class PostingParagraphIterator
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

}

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

function storePosting(&$posting)
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
}

function getRootPosting($grp,$topic_id,$up)
{
global $rootPostingPerms;

if($up>0)
  {
  $msg=getPermsById('messages',$up);
  $group_id=$msg->getGroupId();
  $perms=$msg->getPerms();
  }
else if($topic_id>0)
  {
  $topic=getPermsById('topics',$topic_id);
  $group_id=$topic->getGroupId();
  $perms=$rootPostingPerms;
  }
else
  {
  $group_id=0;
  $perms=$rootPostingPerms;
  }
return newGrpPosting($grp,array('id'       => 0,
				'topic_id' => $topic_id,
				'up'       => $up,
				'group_id' => $group_id,
				'perms'    => $perms));
}

function getPostingById($id=-1,$grp=GRP_ALL,$index1=-1,$topic_id=-1,
                        $fields=SELECT_GENERAL,$up=-1)
{
/* Select */
$imageFields=($fields & SELECT_IMAGES)!=0 ?
             "images.image_set as image_set,images.id as image_id,
	      length(images.large) as image_size,images.large_x as image_x,
	      images.large_y as image_y,images.has_large as has_large_image,
	      images.title as title," :
	     "stotexts.image_set as image_set,";
$topicFields=($fields & SELECT_TOPICS)!=0 ?
             "topics.name as topic_name,topictexts.body as topic_description," :
	     "";
$answersFields=($fields & SELECT_ANSWERS)!=0 ?
	     "messages.answers as answer_count,
	      messages.hidden_answers as hidden_answers,
	      messages.last_answer as last_answer," :
	     "";

$Select="postings.id as id,messages.track as track,postings.ident as ident,
         postings.message_id as message_id,messages.up as up,
	 messages.stotext_id as stotext_id,stotexts.body as body,
	 stotexts.large_format as large_format,
	 stotexts.large_filename as large_filename,
	 stotexts.large_imageset as large_imageset,
	 stotexts.large_body as large_body,messages.lang as lang,
	 messages.subject as subject,messages.author as author,
	 messages.source as source,messages.comment0 as comment0,
	 messages.comment1 as comment1,messages.url as url,grp,priority,
	 postings.index0 as index0,postings.index1 as index1,
	 postings.index2 as index2,subdomain,shadow,messages.sent as sent,
	 topic_id,personal_id,messages.sender_id as sender_id,
	 messages.group_id as group_id,
	 messages.perms as perms,if((messages.perms & 0x1100)=0,1,0) as hidden,
	 messages.disabled as disabled,messages.modbits as modbits,
	 $imageFields
	 $topicFields
	 users.hidden as sender_hidden,login,gender,email,hide_email,rebe,
	 vote,vote_count,
	 $answersFields
	 if(messages.url_check_success=0,0,
	    unix_timestamp()-unix_timestamp(messages.url_check_success))
							   as url_fail_time";
/* From */
$imageTables=($fields & SELECT_IMAGES)!=0 ?
	     "left join images
		   on stotexts.image_set=images.image_set
		      and images.image_set<>0" :
	     "";
$topicTables=($fields & SELECT_TOPICS)!=0 ?
	     "left join topics
		   on postings.topic_id=topics.id
	      left join stotexts as topictexts
		   on topictexts.id=topics.stotext_id" :
	     "";

$From="postings
       left join messages
            on postings.message_id=messages.id
       left join stotexts
            on stotexts.id=messages.stotext_id
       $imageTables
       $topicTables
       left join users
            on messages.sender_id=users.id";
/* Where */
$hideMessages=messagesPermFilter(PERM_READ,'messages');
$grpFilter=grpFilter($grp);
$topicFilter=$topic_id>=0 ? "and postings.topic_id=$topic_id" : '';
$filter=$id>=0 ? "postings.id=$id"
               : ($index1>=0 ? "postings.index1=$index1 and $grpFilter
	                        $topicFilter"
	                     : '');

$Where="$hideMessages and $filter";
/* Query */
$result=sql("select $Select
	     from $From
	     where $Where",
	    'getPostingById');
/* Result */
if(mysql_num_rows($result)>0)
  {
  $row=mysql_fetch_assoc($result);

  global $userId;

  if(($fields & SELECT_ANSWERS)!=0 && $row['hidden_answers']>0 && $userId>0)
    {
    $info=getForumAnswersInfoByMessageId($row['message_id']);
    $row=array_merge($row,$info);
    }
  return newPosting($row);
  }
else
  return getRootPosting($grp,$topic_id,$up);
}

function getMessageIdByPostingId($id)
{
$result=sql("select message_id
	     from postings
	     where id=$id",
	    'getMessageIdByPostingId');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
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
