<?php
# @(#) $Id$

require_once('lib/dataobject.php');
require_once('lib/selectiterator.php');

class Topic
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

function getName()
{
return $this->name;
}

function getDescription()
{
return $this->description;
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
                      "select distinct id,name,description
		       from topics
		       where hidden<$hide
		       order by name");
}

}

?>
