<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/dataobject.php');
require_once('lib/selectiterator.php');
require_once('lib/tmptexts.php');
require_once('lib/grps.php');
require_once('lib/ident.php');

class Topic
      extends DataObject
{
var $id;
var $up;
var $track;
var $name;
var $full_name;
var $description;
var $hidden;
var $allow;
var $premoderate;
var $ident;
var $message_count;
var $sub_count;

function Topic($row)
{
global $defaultPremoderate;

$this->allow=GRP_ALL;
$this->premoderate=$defaultPremoderate;
$this->DataObject($row);
}

function setup($vars)
{
global $grpNames;

if(!isset($vars['edittag']))
  return;
foreach($this->getCorrespondentVars() as $var)
       $this->$var=htmlspecialchars($vars[$var],ENT_QUOTES);
$this->allow=0;
$this->premoderate=0;
foreach($grpNames as $code => $name)
       {
       if($vars[$name])
         $this->allow|=$code;
       if($vars["premoderate_$name"])
         $this->premoderate|=$code;
       }
if(isset($vars['descriptionid']))
  $this->description=tmpTextRestore($vars['descriptionid']);
}

function getCorrespondentVars()
{
return array('up','name','description','hidden','ident');
}

function getWorldVars()
{
return array('up','track','name','description','hidden','allow','premoderate',
             'ident');
}

function store()
{
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

function getFullName()
{
return $this->full_name;
}

function getDescription()
{
return $this->description;
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

function getSubCount()
{
return $this->sub_count;
}

}

class TopicIterator
      extends SelectIterator
{

function getWhere($grp,$up=0,$prefix='')
{
global $userAdminTopics;

$hide=$userAdminTopics ? 2 : 1;
$uf=$up>=0 ? 'and '.byIdent($up,'topics.up','uptopics.ident') : '';
$gf="and (${prefix}allow & $grp)!=0";
return " where $prefix"."hidden<$hide $uf $gf ";
}

function TopicIterator($query)
{
$this->SelectIterator('Topic',$query);
}

}

class TopicListIterator
      extends TopicIterator
{

function TopicListIterator($grp,$up=0)
{
global $userId,$userModerator;

$hide=$userModerator ? 2 : 1;
$gf=getPackedGrpFilter($grp,'postings.');
$this->TopicIterator(
      "select topics.id as id,topics.up as up,topics.name as name,
              topics.description as description,count(messages.id) as message_count
       from topics
	    left join postings
	         on topics.id=postings.topic_id $gf
	    left join topics as uptopics
	         on uptopics.id=topics.up
            left join messages
	         on postings.message_id=messages.id
 	 	    and (messages.hidden<$hide or sender_id=$userId)
		    and (messages.disabled<$hide or sender_id=$userId)".
       $this->getWhere($grp,$up,'topics.').
      'group by topics.id
       order by topics.name');
      /* здесь нужно поменять, если будут другие ограничения на
	 просмотр TODO */
}

}

class TopicNamesIterator
      extends TopicIterator
{

var $names;

function TopicNamesIterator($grp)
{
$this->TopicIterator('select id,track,name
		      from topics'.
		      $this->getWhere($grp,-1).
		     'order by track,name');
}

function create($row)
{
$this->names[(int)$row['id']]=$row['name'];
$n=strtok($row['track'],' ');
$nm=array();
while($n)
     {
     $nm[]=$this->names[(int)$n];
     $n=strtok(' ');
     }
$row['full_name']=join(' :: ',$nm);
return TopicIterator::create($row);
}

}

function getPremoderateByTopicId($id)
{
global $userAdminTopics,$defaultPremoderate;

$hide=$userAdminTopics ? 2 : 1;
$result=mysql_query("select premoderate
                     from topics
		     where id=$id and hidden<$hide")
	     or die('Ошибка SQL при выборке маски модерирования');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0)
                                 : $defaultPremoderate;
}

function getTopicById($id,$up)
{
global $userAdminTopics;

$hide=$userAdminTopics ? 2 : 1;
$result=mysql_query("select id,up,name,description,hidden,allow,premoderate,
                            ident
		     from topics
		     where ".byIdent($id)." and hidden<$hide")
	     or die('Ошибка SQL при выборке темы');
return new Topic(mysql_num_rows($result)>0
                 ? mysql_fetch_assoc($result)
                 : array('up'          => $up,
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

function getTopicTrackById($id)
{
$result=mysql_query("select track
                     from topics
		     where id=$id")
	     or die('Ошибка SQL при выборке маршрута темы');
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : '';
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
