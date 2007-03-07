<?php
require_once('lib/utils.php');
require_once('lib/array.php');
require_once('lib/image-upload-flags.php');
require_once('conf/grps.php');

function grpArray($grp)
{
global $grpGroups;

if(is_array($grp))
  return $grp;
if($grp===GRP_NONE)
  return array();
if(is_numeric("0$grp")) // Странный глюк...
  return array($grp);
return $grpGroups[$grp];
}

function grpJoin($grp1,$grp2)
{
$grp=grpArray($grp1);
$grp2=grpArray($grp2);
foreach($grp2 as $g)
       if(!in_array($g,$grp))
         $grp[]=$g;
return $grp;
}

function grpDiff($grp1,$grp2)
{
return array_diff(grpArray($grp1),grpArray($grp2));
}

// remake
function getGrpOrder($grp)
{
return round(log($grp)/M_LN2);
}

function isGrpValid($grp)
{
return !is_array($grp) && in_array($grp,grpArray(GRP_ALL));
}

// remake
function getGrpWord($grp,$words)
{
return $words[getGrpValid($grp) ? getGrpOrder($grp)+1 : 0];
}

// remake
function getGrpPlural($n,$grp,$words)
{
$pl=array();
$w=getGrpValid($grp) ? (getGrpOrder($grp)+1)*3 : 0;
for($i=0;$i<3;$i++)
   $pl[$i]=$words[$w+$i];
return getPlural($n,$pl);
}

function grpFilter($grp,$field='grp',$prefix='')
{
if($grp===GRP_NONE)
  return 0;
if($grp===GRP_ALL)
  return 1;
$grp=grpArray($grp);
if($prefix!='' && substr($prefix,-1)!='.')
  $prefix.='.';
$conds=array();
foreach($grp as $i)
       $conds[]="${prefix}$field=$i";
return '('.join(' or ',$conds).')';
}

// remake
function grpSample($grp)
{
if($grp==GRP_NONE)
  return 0;
for($i=1;$i<=GRP_ALL;$i*=2)
   if(($i & $grp)!=0)
     return $i;
return 0;
}

class GrpEditor
{
var $ident;
var $title;
var $comment;
var $mandatory;
var $style;
var $base;

var $imageExactX=0;
var $imageExactY=0;
var $imageMaxX=0;
var $imageMaxY=0;
var $thumbExactX=0;
var $thumbExactY=0;
var $thumbMaxX=0;
var $thumbMaxY=0;

function GrpEditor($props)
{
foreach($props as $key => $value)
       $this->$key=$value;
}

function getIdent()
{
return $this->ident;
}

function getTitle()
{
return $this->title;
}

function getComment()
{
return $this->comment;
}

function isMandatory()
{
return $this->mandatory;
}

function getStyle()
{
return $this->style;
}

function getUploadFlags()
{
return imageUploadFlags($this->getStyle());
}

function getBase()
{
return $this->base;
}

function getImageExactX()
{
return $this->imageExactX;
}

function getImageExactY()
{
return $this->imageExactY;
}

function getImageMaxX()
{
return $this->imageMaxX;
}

function getImageMaxY()
{
return $this->imageMaxY;
}

function getThumbExactX()
{
return $this->thumbExactX;
}

function getThumbExactY()
{
return $this->thumbExactY;
}

function getThumbMaxX()
{
return $this->thumbMaxX;
}

function getThumbMaxY()
{
return $this->thumbMaxY;
}

}

function grpEditor($grp=GRP_NONE,$inverse=false)
{
global $grpGetGrpEditor;

if(!$inverse)
  return isset($grpGetGrpEditor[$grp]) ? $grpGetGrpEditor[$grp]
				       : $grpGetGrpEditor[GRP_NONE];
else
  {
  if($grp==GRP_NONE || !isset($grpGetGrpEditor[$grp]))
    return array();
  $remove=array();
  foreach($grpGetGrpEditor[$grp] as $item)
	 if($item['ident']!='')
	   $remove[$item['ident']]=true;
  $editor=array();
  foreach($grpGetGrpEditor[GRP_NONE] as $item)
	 if($item['ident']!='' && !isset($remove[$item['ident']]))
	   $editor[]=$item;
  return $editor;
  }
}

class GrpEditorIterator
      extends MArrayIterator
{

function GrpEditorIterator($grp=GRP_NONE,$inverse=false)
{
parent::MArrayIterator(grpEditor($grp,$inverse));
}

function create($key,$value)
{
return new GrpEditor($value);
}

}
?>
