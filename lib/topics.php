<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/usertag.php');
require_once('lib/selectiterator.php');
require_once('lib/bug.php');
require_once('lib/tmptexts.php');
require_once('lib/grps.php');
require_once('lib/ident.php');
require_once('lib/stotext.php');
require_once('lib/array.php');
require_once('lib/track.php');
require_once('lib/charsets.php');
require_once('lib/grpiterator.php');

class Topic
      extends UserTag
{
var $id;
var $up;
var $track;
var $name;
var $full_name;
var $user_id;
var $stotext;
var $hidden;
var $allow;
var $premoderate;
var $ident;
var $message_count;
var $last_message;
var $sub_count;
var $separate;

function Topic($row)
{
global $defaultPremoderate;

$this->allow=GRP_ALL;
$this->premoderate=$defaultPremoderate;
$this->UserTag($row);
$this->stotext=new Stotext($row,'description');
}

function setup($vars)
{
if(!isset($vars['edittag']))
  return;
foreach($this->getCorrespondentVars() as $var)
       $this->$var=htmlspecialchars($vars[$var],ENT_QUOTES);
$this->allow=0;
$this->premoderate=0;
$iter=new GrpIterator();
while($group=$iter->next())
     if(($group->getGrp() & GRP_ALL)!=0)
       {
       $name=$group->getGrpIdent();
       if($vars[$name])
	 $this->allow|=$group->getGrp();
       if($vars["premoderate_$name"])
	 $this->premoderate|=$group->getGrp();
       }
$this->stotext->setup($vars,'description');
}

function getCorrespondentVars()
{
return array('up','name','hidden','ident','login','separate');
}

function getWorldVars()
{
return array('up','track','name','user_id','hidden','allow','premoderate',
             'ident','separate');
}

function getJencodedVars()
{
return array('up' => 'topics','name' => '','name_sort' => '',
             'user_id' => 'users','stotext_id' => 'stotexts');
}

function getNormal($isAdmin=false)
{
$normal=UserTag::getNormal($isAdmin);
$normal['stotext_id']=$this->stotext->getId();
$normal['name_sort']=convertSort($normal['name']);
return $normal;
}

function store()
{
$result=$this->stotext->store();
if(!$result)
  return $result;
$normal=$this->getNormal();
if($this->id)
  {
  $result=mysql_query(makeUpdate('topics',$normal,array('id' => $this->id)));
  journal(makeUpdate('topics',
                     jencodeVars($normal,$this->getJencodedVars()),
		     array('id' => $this->id)));
  }
else
  {
  $result=mysql_query(makeInsert('topics',$normal));
  $this->id=mysql_insert_id();
  journal(makeInsert('topics',
                     jencodeVars($normal,$this->getJencodedVars())),
	  'topics',$this->id);
  }
journal("track topics ".journalVar('topics',$this->id));
return $result;
}

function getId()
{
return $this->id;
}

function getUpValue()
{
return $this->up;
}

function getName()
{
return $this->name;
}

function getNbName()
{
return str_replace(' ','&nbsp;',$this->getName());
}

function getFullName()
{
return $this->full_name;
}

function getUserId()
{
return $this->user_id;
}

function getStotext()
{
return $this->stotext;
}

function getDescription()
{
return $this->stotext->getBody();
}

function getHTMLDescription()
{
return stotextToHTML(TF_PLAIN,$this->getDescription());
}

function getLargeFilename()
{
return $this->stotext->getLargeFilename();
}

function getLargeFormat()
{
return $this->stotext->getLargeFormat();
}

function getLargeDescription()
{
return $this->stotext->getLargeBody();
}

function getHTMLLargeDescription()
{
return stotextToHTML($this->getLargeFormat(),$this->getLargeDescription());
}

function isHidden()
{
return $this->hidden;
}

function getAllow()
{
return $this->allow;
}

function getPremoderate()
{
return $this->premoderate;
}

function getIdent()
{
return $this->ident;
}

function isSeparate()
{
return $this->separate!=0;
}

function getMessageCount()
{
return $this->message_count;
}

function getLastMessage()
{
return !empty($this->last_message) ? strtotime($this->last_message) : 0;
}

function getSubCount()
{
return $this->sub_count;
}

}

class TopicIterator
      extends SelectIterator
{

function getWhere($grp,$up=0,$prefix='',$withAnswers=false,$recursive=false,
                  $withSeparate=true)
{
global $userAdminTopics;

$hide=$userAdminTopics ? 2 : 1;
$userFilter=$up>=0 ? 'and topics.'.subtree($up,$recursive,'up') : '';
$grpFilter="and (${prefix}allow & $grp)!=0";
$answerFilter=$withAnswers ? 'and forummesgs.id is not null' : '';
$sepFilter=!$withSeparate ? "and ${prefix}separate=0" : '';
return " where ${prefix}hidden<$hide $userFilter $grpFilter $answerFilter $sepFilter ";
}

function TopicIterator($query)
{
$this->SelectIterator('Topic',$query);
}

}

class TopicListIterator
      extends TopicIterator
{

function TopicListIterator($grp,$up=0,$withPostings=false,$withAnswers=false,
                           $subdomain=-1,$withSeparate=true)
{
global $userId,$userModerator;

$hide=$userModerator ? 2 : 1;
$postFilter=$withPostings ? 'having message_count<>0' : '';
$subdomainFilter=$subdomain>=0 ? "and postings.subdomain=$subdomain" : '';
$this->TopicIterator(
      "select topics.id as id,topics.ident as ident,topics.up as up,
              topics.name as name,topics.stotext_id as stotext_id,
	      stotexts.body as description,
	      count(distinct messages.id) as message_count,
	      max(messages.sent) as last_message
       from topics
            left join stotexts
	         on stotexts.id=topics.stotext_id
	    left join postings
	         on topics.id=postings.topic_id and (postings.grp & $grp)<>0
		    $subdomainFilter
	    left join topics as uptopics
	         on uptopics.id=topics.up
            left join messages
	         on postings.message_id=messages.id
 	 	    and (messages.hidden<$hide or messages.sender_id=$userId)
		    and (messages.disabled<$hide or messages.sender_id=$userId)
	    left join forums
		 on messages.id=forums.parent_id
	    left join messages as forummesgs
		 on forums.message_id=forummesgs.id and
		    (forummesgs.hidden<$hide or
		     forummesgs.sender_id=$userId) and
		    (forummesgs.disabled<$hide or
		     forummesgs.sender_id=$userId)".
       $this->getWhere($grp,$up,'topics.',$withAnswers,false,$withSeparate).
      "group by topics.id
       $postFilter
       order by topics.name_sort");
      /* здесь нужно поменять, если будут другие ограничения на
	 просмотр TODO */
}

}

class TopicNamesIterator
      extends TopicIterator
{
var $names;
var $up;
var $delimiter;

function TopicNamesIterator($grp,$up=-1,$recursive=false,$delimiter=' :: ')
{
$this->up=idByIdent('topics',$up);
$this->delimiter=$delimiter;
$this->TopicIterator('select id,track,name
		      from topics'.
		      $this->getWhere($grp,$this->up,'',false,$recursive).
		    ' order by track');
}

function create($row)
{
$this->names[(int)$row['id']]=$row['name'];
$n=strtok($row['track'],' ');
$nm=array();
$up=$this->up;
while($n)
     {
     if($up<=0)
       $nm[]=$this->names[(int)$n];
     else
       if((int)$n==$up)
	 $up=-1;
     $n=strtok(' ');
     }
$row['full_name']=join($this->delimiter,$nm);
return TopicIterator::create($row);
}

}

class SortedTopicNamesIterator
      extends ArrayIterator
{

function SortedTopicNamesIterator($grp,$up=-1,$recursive=false,
                                  $delimiter=' :: ')
{
$iterator=new TopicNamesIterator($grp,$up,$recursive,$delimiter);
$topics=array();
while($item=$iterator->next())
     $topics[$item->getFullName()]=$item;
setlocale('LC_COLLATE','ru_RU.KOI8-R');
uksort($topics,'strcoll');
$this->ArrayIterator($topics);
}

}

class TopicHierarchyIterator
      extends ArrayIterator
{

function TopicHierarchyIterator($topic_id,$root=-1,$reverse=false)
{
$root=idByIdent('topics',$root);
$topics=array();
for($id=idByIdent('topics',$topic_id);$id>0 && $id!=$root;)
   {
   $topic=getTopicById($id,0);
   $topics[]=$topic;
   $id=$topic->getUpValue();
   }
if(!$reverse)
  $topics=array_reverse($topics);
$this->ArrayIterator($topics);
}

}

class CrossTopicIterator
      extends SelectIterator
{

function CrossTopicIterator($cross,$grp=GRP_ALL)
{
$grpFilter=$grp!=GRP_ALL ? "and (allow & $grp)<>0" : '';
$this->SelectIterator('Topic',
                      "select id,name
		       from topics
		            left join cross_topics
			         on topics.id=cross_topics.peer_id
		       where topic_id=$cross $grpFilter
		       order by track");
}

}

function getAllowByTopicId($id)
{
global $userAdminTopics;

$hide=$userAdminTopics ? 2 : 1;
$result=mysql_query("select allow
                     from topics
		     where id=$id and hidden<$hide")
          or sqlbug('Ошибка SQL при выборке маски допустимых постингов');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0)
                                 : GRP_ALL;
}

function getPremoderateByTopicId($id)
{
global $userAdminTopics,$defaultPremoderate;

$hide=$userAdminTopics ? 2 : 1;
$result=mysql_query("select premoderate
                     from topics
		     where id=$id and hidden<$hide")
          or sqlbug('Ошибка SQL при выборке маски модерирования');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0)
                                 : $defaultPremoderate;
}

function getTopicById($id,$up)
{
global $userId,$userAdminTopics;

$hide=$userAdminTopics ? 2 : 1;
$result=mysql_query(
       "select topics.id as id,topics.up as up,topics.name as name,
               topics.stotext_id as stotext_id,
               stotexts.body as description,image_set,
	       large_filename,large_format,
	       stotexts.large_body as large_description,
	       large_imageset,topics.hidden as hidden,topics.allow as allow,
	       topics.premoderate as premoderate,topics.ident as ident,
	       topics.separate as separate,
	       max(messages.sent) as last_message,
	       topics.user_id as user_id,login,gender,email,hide_email,rebe,
	       count(distinct subtopics.id) as sub_count
	from topics
	     left join users
	          on topics.user_id=users.id
	     left join stotexts
		  on topics.stotext_id=stotexts.id
	     left join topics as subtopics
	          on subtopics.up=topics.id
	     left join postings
	          on postings.topic_id=topics.id
	     left join messages
		  on postings.message_id=messages.id
		     and (messages.hidden<$hide or messages.sender_id=$userId)
		     and (messages.disabled<$hide or messages.sender_id=$userId)
	where topics.id=$id and topics.hidden<$hide
	group by topics.id")
 or sqlbug('Ошибка SQL при выборке темы'.mysql_error());
return new Topic(mysql_num_rows($result)>0
                 ? mysql_fetch_assoc($result)
                 : array('up'          => idByIdent('topics',$up),
		         'allow'       => getAllowByTopicId($up),
                         'premoderate' => getPremoderateByTopicId($up)));
}

function getTopicNameById($id)
{
global $userAdminTopics;

$hide=$userAdminTopics ? 2 : 1;
$result=mysql_query("select id,name
		     from topics
		     where id=$id and topics.hidden<$hide")
	  or sqlbug('Ошибка SQL при выборке названия темы');
return new Topic(mysql_num_rows($result)>0 ? mysql_fetch_assoc($result)
					   : array());
}

function getTopicOwnerById($id)
{
$track=trackById('topics',$id);
$result=mysql_query("select user_id
                     from topics
		     where '$track' like concat(track,'%') and user_id<>0
		     order by length(track) desc
		     limit 1")
	  or sqlbug('Ошибка SQL при выборке владельца темы');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : 0;
}

function getSubtopicsCountById($id,$recursive=false)
{
$id=idByIdent('topics',$id);
$result=mysql_query('select count(*)
                     from topics
		     where '.subtree($id,$recursive,'up'))
	  or sqlbug('Ошибка SQL при получении количества подтем');
return mysql_num_rows($result)>0
       ? mysql_result($result,0,0)-($recursive ? 1 : 0) : 0;
}

function topicExists($id)
{
global $userAdminTopics;

$hide=$userAdminTopics ? 2 : 1;
$result=mysql_query("select id
		     from topics
		     where id=$id and hidden<$hide")
	  or sqlbug('Ошибка SQL при проверке наличия темы');
return mysql_num_rows($result)>0;
}
?>
