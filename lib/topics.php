<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/dataobject.php');
require_once('lib/selectiterator.php');
require_once('lib/tmptexts.php');
require_once('lib/grps.php');
require_once('lib/ident.php');
require_once('lib/stotext.php');
require_once('lib/array.php');
require_once('lib/track.php');

class Topic
      extends DataObject
{
var $id;
var $up;
var $track;
var $name;
var $full_name;
var $stotext;
var $hidden;
var $allow;
var $premoderate;
var $ident;
var $message_count;
var $last_message;
var $sub_count;

function Topic($row)
{
global $defaultPremoderate;

$this->allow=GRP_ALL;
$this->premoderate=$defaultPremoderate;
$this->DataObject($row);
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
$grpNames=$this->getGrpVars();
foreach($grpNames as $code => $name)
       {
       if($vars[$name])
         $this->allow|=$code;
       if($vars["premoderate_$name"])
         $this->premoderate|=$code;
       }
$this->stotext->setup($vars,'description');
}

function getCorrespondentVars()
{
return array('up','name','hidden','ident');
}

function getGrpVars()
{
return array(GRP_NEWS     => 'news',
             GRP_FORUMS   => 'forums',
	     GRP_GALLERY  => 'gallery',
	     GRP_ARTICLES => 'articles');
}

function getWorldVars()
{
return array('up','track','name','hidden','allow','premoderate','ident');
}

function getNormal($isAdmin=false)
{
$normal=DataObject::getNormal($isAdmin);
$normal['stotext_id']=$this->stotext->getId();
return $normal;
}

function store()
{
$result=$this->stotext->store();
if(!$result)
  return $result;
$normal=$this->getNormal();
$result=mysql_query($this->id 
                    ? makeUpdate('topics',$normal,array('id' => $this->id))
                    : makeInsert('topics',$normal));
if(!$this->id)
  $this->id=mysql_insert_id();
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

function isNews()
{
return ($this->getAllow() & GRP_NEWS)!=0;
}

function isForums()
{
return ($this->getAllow() & GRP_FORUMS)!=0;
}

function isGallery()
{
return ($this->getAllow() & GRP_GALLERY)!=0;
}

function isArticles()
{
return ($this->getAllow() & GRP_ARTICLES)!=0;
}

function getPremoderate()
{
return $this->premoderate;
}

function isNewsPremoderated()
{
return ($this->getPremoderate() & GRP_NEWS)!=0;
}

function isForumsPremoderated()
{
return ($this->getPremoderate() & GRP_FORUMS)!=0;
}

function isGalleryPremoderated()
{
return ($this->getPremoderate() & GRP_GALLERY)!=0;
}

function isArticlesPremoderated()
{
return ($this->getPremoderate() & GRP_ARTICLES)!=0;
}

function getIdent()
{
return $this->ident;
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

function getWhere($grp,$up=0,$prefix='',$withAnswers=false)
{
global $userAdminTopics;

$hide=$userAdminTopics ? 2 : 1;
$uf=$up>=0 ? 'and '.byIdent($up,'topics.up','uptopics.ident') : '';
$gf="and (${prefix}allow & $grp)!=0";
$af=$withAnswers ? "and forummesgs.id is not null" : '';
return " where $prefix"."hidden<$hide $uf $gf $af ";
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
                           $cols=5)
{
global $userId,$userModerator;

$this->cols=$cols;
$hide=$userModerator ? 2 : 1;
$postFilter=$withPostings ? 'having message_count<>0' : '';
$this->TopicIterator(
      "select topics.id as id,topics.up as up,topics.name as name,
              topics.stotext_id as stotext_id,stotexts.body as description,
	      count(distinct messages.id) as message_count,
	      max(messages.sent) as last_message
       from topics
            left join stotexts
	         on stotexts.id=topics.stotext_id
	    left join postings
	         on topics.id=postings.topic_id and (postings.grp & $grp)<>0
	    left join topics as uptopics
	         on uptopics.id=topics.up
            left join messages
	         on postings.message_id=messages.id
 	 	    and (messages.hidden<$hide or messages.sender_id=$userId)
		    and (messages.disabled<$hide or messages.sender_id=$userId)
	    left join forums
		 on messages.id=forums.up
	    left join messages as forummesgs
		 on forums.message_id=forummesgs.id and
		    (forummesgs.hidden<$hide or
		     forummesgs.sender_id=$userId) and
		    (forummesgs.disabled<$hide or
		     forummesgs.sender_id=$userId)".
       $this->getWhere($grp,$up,'topics.',$withAnswers).
      "group by topics.id
       $postFilter
       order by topics.name");
      /* здесь нужно поменять, если будут другие ограничения на
	 просмотр TODO */
}

function isEol()
{
return ($this->getPosition() % $this->cols)==$this->cols-1;
}

}

class TopicNamesIterator
      extends TopicIterator
{
var $names;
var $up;

function TopicNamesIterator($grp,$up=-1)
{
$this->up=$up<0 ? $up : idByIdent('topics',$up);
$this->TopicIterator('select id,track,name
		      from topics'.
		      $this->getWhere($grp,$this->up).
		     'order by track');
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
$row['full_name']=join(' :: ',$nm);
return TopicIterator::create($row);
}

}

class SortedTopicNamesIterator
      extends ArrayIterator
{

function SortedTopicNamesIterator($grp,$up=-1)
{
$iterator=new TopicNamesIterator($grp,$up);
$topics=array();
while($item=$iterator->next())
     $topics[$item->getFullName()]=$item;
setlocale('LC_COLLATE','ru_RU.KOI8-R');
uksort($topics,'strcoll');
$this->ArrayIterator($topics);
}

}

function getPremoderateByTopicId($id)
{
global $userAdminTopics,$defaultPremoderate;

$hide=$userAdminTopics ? 2 : 1;
$result=mysql_query('select premoderate
                     from topics
		     where '.byIdent($id)." and hidden<$hide")
	     or die('Ошибка SQL при выборке маски модерирования');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0)
                                 : $defaultPremoderate;
}

function getTopicById($id,$up)
{
global $userAdminTopics;

$hide=$userAdminTopics ? 2 : 1;
$result=mysql_query('select topics.id as id,up,name,stotext_id,
                            stotexts.body as description,image_set,
			    large_filename,large_format,
			    stotexts.large_body as large_description,
			    large_imageset,hidden,allow,premoderate,ident
		     from topics
		          left join stotexts
			       on topics.stotext_id=stotexts.id
		     where topics.'.byIdent($id)." and hidden<$hide")
	     or die('Ошибка SQL при выборке темы');
return new Topic(mysql_num_rows($result)>0
                 ? mysql_fetch_assoc($result)
                 : array('up'          => idByIdent('topics',$up),
                         'premoderate' => getPremoderateByTopicId($up)));
}

function getTopicNameById($id)
{
global $userAdminTopics;

$hide=$userAdminTopics ? 2 : 1;
$result=mysql_query('select id,name
		     from topics
		     where '.byIdent($id)." and topics.hidden<$hide")
	     or die('Ошибка SQL при выборке названия темы');
return new Topic(mysql_num_rows($result)>0 ? mysql_fetch_assoc($result)
					   : array());
}

function topicExists($id)
{
global $userAdminTopics;

$hide=$userAdminTopics ? 2 : 1;
$result=mysql_query("select id
		     from topics
		     where id=$id and hidden<$hide")
	     or die('Ошибка SQL при выборке темы');
return mysql_num_rows($result)>0;
}
?>
