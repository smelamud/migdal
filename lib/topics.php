<?php
# @(#) $Id$

require_once('lib/dataobject.php');
require_once('lib/selectiterator.php');

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
if(!isset($vars['hidden']))
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

class TopicListIterator
      extends SelectIterator
{

function TopicListIterator()
{
global $userAdminTopics;

$hide=$userAdminTopics ? 2 : 1;
$this->SelectIterator('Topic',
                      "select id,name,description
		       from topics
		       where hidden<$hide
		       order by name");
}

}

function getTopicById($id)
{
global $userAdminTopics;

$hide=$userAdminTopics ? 2 : 1;
$result=mysql_query("select id,name,description,hidden,no_news,no_forums,
                            no_gallery
		     from topics
		     where id=$id and hidden<$hide");
return new Topic(mysql_num_rows($result)>0 ? mysql_fetch_assoc($result)
                                           : array());
}
?>
