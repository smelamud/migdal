<?php
# @(#) $Id$

require_once('lib/iterator.php');
require_once('lib/redirs.php');
require_once('lib/utils.php');
require_once('lib/uri.php');
require_once('lib/ident.php');
require_once('lib/entries.php');
require_once('lib/cross-entries.php');
require_once('lib/cache.php');

require_once('grp/titles.php');
require_once('grp/scripts.php');
require_once('grp/traps.php');
require_once('grp/structure.php');

function idOrCatalog($id,$catalog)
{
$id=normalizePath($id,true,SLASH_NO,SLASH_NO);
$pos=strrpos($id,'/');
if($pos!==false)
  $id=substr($id,$pos+1);
if(isId($id))
  return (int)$id;
$catalog=strtr($catalog,'.','/');
$catalog=normalizePath($catalog,true,SLASH_NO,SLASH_NO);
$catalog=strtr($catalog,'/','.');
return idByIdent($catalog);
}

function isEntryInGrp($id,$grp)
{
$grp=grpArray($grp);
$egrp=getGrpByEntryId($id);
if($egrp<=0 || !in_array($egrp,$grp))
  {
  $egrps=getGrpsByEntryId($id);
  foreach($egrps as $egrp)
         if(in_array($egrp,$grp))
	   return true;
  return false;
  }
else
  return true;
}

function &getParentLocationInfo($path,$redirid)
{
if($redirid!=0)
  {
  $redir=getRedirById($redirid);
  $parts=parse_url($redir->getURI());
  $info=&getLocationInfo($parts['path'],$redir->getUp());
  $info->setRedir($redir);
  return $info;
  }
else
  return getLocationInfo($path,0);
}

class LocationInfo
{
var $path;
var $script;
var $args=array();
var $ids=array();
var $redir=null;
var $title='Untitled';
var $titleRelative='Untitled';
var $titleFull='Untitled';
var $parent=null;
var $child=null;
var $orig=null;
var $linkName='';
var $linkId=0;
var $linkTitle='';
var $linkIcon='';

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

function getArg($name)
{
return $this->args[$name];
}

function getIds()
{
return $this->ids;
}

function setIds($ids)
{
$this->ids=$ids;
}

function getId($name)
{
return $this->ids[$name];
}

function origHasIds($ids)
{
$orig=$this->getOrig();
foreach($ids as $id)
       if(!isset($orig->ids[$id]))
         return false;
return true;
}

function getRedir()
{
return $this->redir;
}

function setRedir($redir)
{
$this->redir=$redir;
}

function getURI()
{
if($this->redir!=null)
  return $this->redir->getURI();
else
  if($this->child==null)
    return $_SERVER['REQUEST_URI'];
  else
    return $this->path;
}

function getRedirId()
{
if($this->redir!=null)
  return $this->redir->getId();
else
  return 0;
}

function getTitle()
{
return $this->title;
}

function setTitle($title)
{
$this->title=$title;
}

function getTitleRelative()
{
return $this->titleRelative;
}

function setTitleRelative($title)
{
$this->titleRelative=$title;
}

function getTitleFull()
{
return $this->titleFull;
}

function setTitleFull($title)
{
$this->titleFull=$title;
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

function getParentURI()
{
if($this->parent!=null)
  return $this->parent->getURI();
else
  return '';
}

function &getChild()
{
return $this->child;
}

function &getOrig()
{
return $this->orig;
}

function setOrig(&$orig)
{
$this->orig=&$orig;
}

function getLinkName()
{
return $this->linkName;
}

function setLinkName($linkName)
{
$this->linkName=$linkName;
}

function getLinkId()
{
return $this->linkId;
}

function setLinkId($linkId)
{
$this->linkId=$linkId;
}

function getLinkTitle()
{
return $this->linkTitle;
}

function setLinkTitle($linkTitle)
{
$this->linkTitle=$linkTitle;
}

function getLinkIcon()
{
return $this->linkIcon;
}

function setLinkIcon($linkIcon)
{
$this->linkIcon=$linkIcon;
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

function LocationIterator($offset=0)
{
global $LocationInfo;

parent::Iterator();
$this->current=&$LocationInfo->getRoot();
$this->count=0;
for($i=0;$i<$offset && $this->current!=null;$i++)
   $this->current=&$this->current->getChild();
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
