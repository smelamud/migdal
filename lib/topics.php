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
return array('no_news' => 'news','no_forums' => 'forums',
             'no_gallery' => 'gallery');
}

function getWorldVars()
{
return array('name','description','hidden','no_news','no_forums','no_gallery');
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

}

class GroupSelectIterator
      extends SelectIterator
{

function getGrpCondition($grp)
{
return $grp==GRP_ANY ? ''
                     : 'and ('.join(' or ',
		               $this->noEqZero(getGrpNames($grp))).')';
}

function noEqZero($vars)
{
$conds=array();
foreach($vars as $var)
       $conds[]="no_$var=0";
return $conds;
}

}

class BaseTopicListIterator
      extends GroupSelectIterator
{

function BaseTopicListIterator($sql)
{
$this->GroupSelectIterator('Topic',$sql);
}

function getWhere($grp)
{
global $userAdminTopics;

$hide=$userAdminTopics ? 2 : 1;
$grpFilter=$this->getGrpCondition($grp);
return " where hidden<$hide $grpFilter ";
}

}

class TopicListIterator
      extends BaseTopicListIterator
{

function TopicListIterator($grp)
{
$this->BaseTopicListIterator('select id,name,description
		              from topics'
			      .$this->getWhere($grp).
		             'order by name');
}

}

class TopicNamesIterator
      extends BaseTopicListIterator
{

function TopicNamesIterator($grp)
{
$this->BaseTopicListIterator('select id,name
		              from topics'
			      .$this->getWhere($grp).
		             'order by name');
}

}

function getTopicById($id)
{
global $userAdminTopics;

$hide=$userAdminTopics ? 2 : 1;
$result=mysql_query("select id,name,description,hidden,no_news,no_forums,
                            no_gallery
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
