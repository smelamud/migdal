<?php
# @(#) $Id$

require_once('lib/dataobject.php');
require_once('lib/selectiterator.php');
require_once('lib/tmptexts.php');
require_once('lib/grps.php');

class Topic
      extends DataObject
{
var $id;
var $name;
var $description;
var $hidden;
var $no_news;
var $no_forums;
var $no_gallery;
var $no_articles;
var $message_count;

function Topic($row)
{
$this->DataObject($row);
}

function setup($vars)
{
if(!isset($vars['edittag']))
  return;
foreach($this->getCorrespondentVars() as $var)
       $this->$var=htmlspecialchars($vars[$var],ENT_QUOTES);
foreach($this->getInverseVars() as $var => $inv_var)
       $this->$var=$vars[$inv_var] ? 0 : 1;
if(isset($vars['descriptionid']))
  $this->description=tmpTextRestore($vars['descriptionid']);
}

function getCorrespondentVars()
{
return array('name','description','hidden');
}

function getInverseVars()
{
return array('no_news' => 'news',
             'no_forums' => 'forums',
             'no_gallery' => 'gallery',
             'no_articles' => 'articles');
}

function getWorldVars()
{
return array('name','description','hidden','no_news','no_forums','no_gallery',
             'no_articles');
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

function getName()
{
return $this->name;
}

function getDescription()
{
return $this->description;
}

function isHidden()
{
return $this->hidden;
}

function isNews()
{
return $this->no_news ? 0 : 1;
}

function isForums()
{
return $this->no_forums ? 0 : 1;
}

function isGallery()
{
return $this->no_gallery ? 0 : 1;
}

function isArticles()
{
return $this->no_articles ? 0 : 1;
}

function getMessageCount()
{
return $this->message_count;
}

}

class TopicIterator
      extends SelectIterator
{

function getWhere($grp,$prefix='')
{
global $userAdminTopics;

$hide=$userAdminTopics ? 2 : 1;
$gf=getUnpackedGrpFilter($grp,$prefix);
return " where $prefix"."hidden<$hide $gf ";
}

function TopicIterator($query)
{
$this->SelectIterator('Topic',$query);
}

}

class TopicListIterator
      extends TopicIterator
{

function TopicListIterator($grp)
{
global $userId,$userModerator;

$hide=$userModerator ? 2 : 1;
$gf=getPackedGrpFilter($grp,'postings.');
$this->TopicIterator(
      "select topics.id as id,topics.name as name,description,
	      count(messages.id) as message_count
       from topics
	    left join postings
	         on topics.id=postings.topic_id $gf
            left join messages
	         on postings.message_id=messages.id
 	 	    and (messages.hidden<$hide or sender_id=$userId)
		    and (messages.disabled<$hide or sender_id=$userId)".
       $this->getWhere($grp,'topics.').
      'group by topics.id
       order by topics.name');
      /* здесь нужно поменять, если будут другие ограничения на
	 просмотр TODO */
}

}

class TopicNamesIterator
      extends TopicIterator
{

function TopicNamesIterator($grp)
{
$this->TopicIterator('select id,name
		      from topics'.
		      $this->getWhere($grp).
		     'order by name');
}

}

function getTopicById($id)
{
global $userAdminTopics;

$hide=$userAdminTopics ? 2 : 1;
$result=mysql_query("select id,name,description,hidden,no_news,no_forums,
                            no_gallery,no_articles
		     from topics
		     where id=$id and hidden<$hide")
	     or die('Ошибка SQL при выборке темы');
return new Topic(mysql_num_rows($result)>0 ? mysql_fetch_assoc($result)
                                           : array());
}

function getTopicNameById($id)
{
global $userAdminTopics;

$hide=$userAdminTopics ? 2 : 1;
$result=mysql_query("select id,name
		     from topics
		     where id=$id and hidden<$hide")
	     or die('Ошибка SQL при выборке темы');
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
