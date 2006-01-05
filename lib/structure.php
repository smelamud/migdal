<?php
# @(#) $Id$

require_once('lib/iterator.php');

require_once('grp/structure.php');

class LocationInfo
{
var $path;
var $script;
var $args=array();
var $title='Title';
var $parent=null;
var $child=null;

function LocationInfo()
{
}

function getPath()
{
return $this->path;
}

function setPath($path)
{
$this->path=$path;
}

function getScript()
{
return $this->script;
}

function setScript($script)
{
$this->script=$script;
}

function getArgs()
{
return $this->args;
}

function setArgs($args)
{
$this->args=$args;
}

function getTitle()
{
return $this->title;
}

function setTitle($title)
{
$this->title=$title;
}

function &getParent()
{
return $this->parent;
}

function setParent(&$parent)
{
$this->parent=&$parent;
if($parent!=null)
  $this->parent->child=&$this;
}

function &getChild()
{
return $this->child;
}

function &getRoot()
{
if($this->parent==null)
  return $this;
else
  return $this->parent->getRoot();
}

}

class LocationIterator
      extends Iterator
{
var $current;
var $count;

function LocationIterator()
{
global $LocationInfo;

parent::Iterator();
$this->current=&$LocationInfo->getRoot();
$this->count=0;
for($p=&$this->current;$p!=null;$p=&$p->getChild())
   $this->count++;
}

function getCount()
{
return $this->count;
}

function isLast()
{
return $this->current==null;
}

function next()
{
parent::next();
$result=&$this->current;
if($this->current!=null)
  $this->current=&$this->current->getChild();
return $result;
}

}
?>
