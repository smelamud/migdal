<?php
# @(#) $Id$

require_once('lib/messages.php');
require_once('lib/stotext-images.php');
require_once('lib/selectiterator.php');
require_once('lib/limitselect.php');
require_once('lib/grps.php');
require_once('lib/topics.php');
require_once('lib/text.php');
require_once('lib/paragraphs.php');
require_once('lib/sort.php');
require_once('lib/track.php');
require_once('lib/random.php');
require_once('lib/users.php');
require_once('lib/bug.php');
require_once('lib/cache.php');
require_once('lib/select.php');
require_once('lib/alphabet.php');
require_once('lib/forums.php');

class Posting
      extends Message
{
var $ident;
var $message_id;
var $topic_id;
var $topic_ident;
var $topic_name;
var $topic_description;
var $personal_id;
var $grp;
var $priority;
var $read_count;
var $vote;
var $vote_count;
var $subdomain;
var $shadow;

function Posting($row)
{
$this->Message($row);
$this->grp=GRP_NONE;
}

function setup($vars)
{
if(!isset($vars['edittag']))
  return;
Message::setup($vars);
$this->topic_id=idByIdent('topics',$vars['topic_id']);
}

function getCorrespondentVars()
{
$list=Message::getCorrespondentVars();
array_push($list,'ident','grp','personal_id','priority','index1','index2');
return $list;
}

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

function getNormalPosting($isAdmin=false)
{
$normal=$this->collectVars($this->getWorldPostingVars());
if($isAdmin)
  $normal=array_merge($normal,$this->collectVars($this->getAdminPostingVars()));
return $normal;
}

function store()
{
global $userModerator;

$result=Message::store('message_id');
if(!$result)
  return $result;
$normal=$this->getNormalPosting($userModerator);
if($this->id)
  {
  $result=mysql_query(makeUpdate('postings',$normal,array('id' => $this->id)));
  journal(makeUpdate('postings',
                     jencodeVars($normal,$this->getJencodedPostingVars()),
		     array('id' => journalVar('postings',$this->id))));
  }
else
  {
  $result=mysql_query(makeInsert('postings',$normal));
  $this->id=mysql_insert_id();
  journal(makeInsert('postings',
                     jencodeVars($normal,$this->getJencodedPostingVars())),
	  'postings',$this->id);
  }
return $result;
}

function isValid()
{
return $this->grp!=GRP_NONE;
}

function getIdent()
{
return $this->ident;
}

function getMessageId()
{
return $this->message_id;
}

function getGrp()
{
return $this->grp;
}

function setGrp($grp)
{
$this->grp=$grp;
}

function getPriority()
{
return $this->priority;
}

function getTopicId()
{
return $this->topic_id;
}

function getTopicIdent()
{
return $this->topic_ident;
}

function getTopicName()
{
return $this->topic_name;
}

function getTopicDescription()
{
return $this->topic_description;
}

function getPersonalId()
{
return $this->personal_id;
}

function getReadCount()
{
return $this->read_count;
}

function getVote()
{
return $this->vote_count==0 ? 2.5 : $this->vote/$this->vote_count;
}

function getVoteCount()
{
return $this->vote_count;
}

function getVoteString()
{
return sprintf("%1.2f",$this->getVote());
}

function getVote20()
{
return (int)round($this->getVote()*4);
}

function getIndex0()
{
return $this->index0;
}

function getIndex1()
{
return $this->index1;
}

function getIndex2()
{
return $this->index2;
}

function getSubdomain()
{
return $this->subdomain;
}

function getShadow()
{
return $this->shadow;
}

}

require_once('grp/postings.php');

function newPosting($row)
{
$name=getGrpClassName($row['grp']);
return new $name($row);
}

function newGrpPosting($grp,$row=array())
{
$name=getGrpClassName($grp);
return new $name($row);
}

function newDetailedPosting($grp,$topic_id=-1,$sender_id=0,$id=0,
                            $topic_name='')
{
return newPosting(array('id'         => $id,
                        'grp'        => $grp,
                        'topic_id'   => $topic_id,
                        'topic_name' => $topic_name,
			'sender_id'  => $sender_id));
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
			     $fields=SELECT_ALLPOSTING,$modbits=MOD_NONE,
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
	      ifnull(last_answer,messages.sent) as age," :
	     "");

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
	 postings.index2 as index2,subdomain,shadow,
	 $imageFields
	 $topicFields
	 login,gender,email,hide_email,rebe,
	 read_count,vote,vote_count,
	 $answersFields
	 if(messages.url_check_success=0,0,
	    unix_timestamp()-unix_timestamp(messages.url_check_success))
	                                            as url_fail_time";
/* From */
$imageTables=($fields & SELECT_IMAGES)!=0 ?
	     "left join images
		   on stotexts.image_set=images.image_set" :
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

$From="postings
       left join messages
	    on postings.message_id=messages.id
       left join stotexts
	    on stotexts.id=messages.stotext_id
       $imageTables
       $topicTables
       left join users
	    on messages.sender_id=users.id
       $answersTables";
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
	     SORT_READ       => 'read_count desc,sent desc',
	     SORT_INDEX0     => 'postings.index0',
	     SORT_INDEX1     => 'postings.index1',
	     SORT_RINDEX1    => 'postings.index1 desc',
	     SORT_RATING     => 'if(vote_count=0,2.5,vote/vote_count) desc,'.
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
$result=mysql_query("select stotext_id,par,image_id,placement,
                            has_large as has_large_image,title,format
		     from stotext_images
		          left join images
			       on images.id=stotext_images.image_id
		     where stotext_id=$sid");
if(!$result)
  sqlbug('Ошибка SQL при выборке иллюстраций к постингу');
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
                        $fields=SELECT_ALLPOSTING,$up=-1)
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
	 read_count,vote,vote_count,
	 $answersFields
	 if(messages.url_check_success=0,0,
	    unix_timestamp()-unix_timestamp(messages.url_check_success))
							   as url_fail_time";
/* From */
$imageTables=($fields & SELECT_IMAGES)!=0 ?
	     "left join images
		   on stotexts.image_set=images.image_set" :
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
$result=mysql_query("select $Select
                     from $From
                     where $Where")
          or sqlbug('Ошибка SQL при выборке постинга');
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

function incPostingReadCount($id)
{
mysql_query("update postings
             set read_count=read_count+1,last_read=now()
	     where id=$id")
  or sqlbug('Ошибка SQL при обновлении счетчика прочтений постинга');
}

function getMessageIdByPostingId($id)
{
$result=mysql_query("select message_id
                     from postings
		     where id=$id")
	  or sqlbug('Ошибка SQL при получении идентификатора сообщения в постинге');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

function getMaxIndexOfPosting($index,$grp,$topic_id=-1,$recursive=false)
{
$hide=messagesPermFilter(PERM_READ,'messages');
$grpFilter=grpFilter($grp);
$topicFilter=($topic_id<0 || $recursive && $topic_id==0)
             ? '' : ' and topics.'.subtree('topics',$topic_id,$recursive);
$result=mysql_query("select max(postings.index$index)
                     from postings
			  left join messages
			       on postings.message_id=messages.id
			  left join topics
			       on postings.topic_id=topics.id
		     where $hide and $grpFilter $topicFilter")
	  or sqlbug('Ошибка SQL при получении максимального индекса постинга');
return mysql_num_rows($result)>0 ? (int)mysql_result($result,0,0) : 0;
}

function getVoteInfoByPostingId($id,$grp=GRP_ALL)
{
$result=mysql_query("select id,vote,vote_count
                     from postings
		     where id=$id")
	  or sqlbug('Ошибка SQL при получении рейтинга постинга');
return mysql_num_rows($result)>0 ? newPosting(mysql_fetch_assoc($result))
                                 : newGrpPosting($grp);
}

function getPostingIdByMessageId($id)
{
$result=mysql_query("select id
                     from postings
		     where message_id=$id")
	  or sqlbug('Ошибка SQL при получении постинга по сообщению');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

function getSibling($up,$index0,$next=true)
{
$filter=$next ? "index0>$index0" : "index0<$index0";
$order=$next ? 'asc' : 'desc';
$result=mysql_query("select postings.id
                     from postings
		          left join messages
			       on postings.message_id=messages.id
		     where up=$up and $filter
		     order by index0 $order
		     limit 1");
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

function getSiblingIndex0($up,$index0,$next=true)
{
$filter=$next ? "index0>$index0" : "index0<$index0";
$order=$next ? 'asc' : 'desc';
$result=mysql_query("select index0
                     from postings
		          left join messages
			       on postings.message_id=messages.id
		     where up=$up and $filter
		     order by index0 $order
		     limit 1");
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : -1;
}

function getSiblingIssue($grp,$topic_id,$index1,$next=true)
{
$grpFilter=grpFilter($grp);
$issueFilter=$next ? "and index1>$index1" : "and index1<$index1";
$order=$next ? 'asc' : 'desc';
$topicFilter=$topic_id>=0 ? "and topic_id=$topic_id" : '';
$result=mysql_query("select postings.id
                     from postings
		          left join messages
			       on postings.message_id=messages.id
		     where $grpFilter $topicFilter $issueFilter
		     order by index1 $order
		     limit 1");
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

function getSiblingIndex1($grp,$topic_id,$index1,$next=true)
{
$grpFilter=grpFilter($grp);
$issueFilter=$next ? "and index1>$index1" : "and index1<$index1";
$order=$next ? 'asc' : 'desc';
$topicFilter=$topic_id>=0 ? "and topic_id=$topic_id" : '';
$result=mysql_query("select index1
                     from postings
		          left join messages
			       on postings.message_id=messages.id
		     where $grpFilter $topicFilter $issueFilter
		     order by index1 $order
		     limit 1");
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

function postingExists($id)
{
$hide=messagesPermFilter(PERM_READ);
$result=mysql_query("select postings.id
		     from postings
		          left join messages
			       on postings.message_id=messages.id
		     where postings.id=$id and $hide")
	  or sqlbug('Ошибка SQL при проверке наличия постинга');
return mysql_num_rows($result)>0;
}

function getPostingDomainCount($topic_id=-1,$recursive=false,$grp=GRP_ALL)
{
$hide=messagesPermFilter(PERM_READ,'messages');
$topicFilter=$topic_id>=0
             ? 'and topics.'.subtree('topics',$topic_id,$recursive) : '';
$grpFilter='and '.grpFilter($grp);
$result=mysql_query(
        "select count(distinct url_domain)
         from messages
	 left join postings
	      on postings.message_id=messages.id
	 left join topics
	      on topics.id=postings.topic_id
         where $hide and shadow=0 $topicFilter $grpFilter")
          or sqlbug('Ошибка SQL при получении количества доменов');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}
?>
