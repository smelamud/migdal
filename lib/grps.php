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
return isset($grpGroups[$grp]) ? $grpGroups[$grp] : array();
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

function isGrpValid($grp)
{
return !is_array($grp) && in_array($grp,grpArray(GRP_ALL));
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
if(count($conds)==0)
  return '1';
else
  return '('.join(' or ',$conds).')';
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

function __construct($props)
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

function getImageStyle()
{
@list($thumbFlag, $imageFlag) = explode('-', $this->getStyle());
return isset($imageFlag) && $imageFlag != '' ? $imageFlag : 'manual';
}

function getThumbnailStyle()
{
@list($thumbFlag, $imageFlag) = explode('-', $this->getStyle());
return isset($thumbFlag) && $thumbFlag != '' ? $thumbFlag : 'auto';
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
        extends MArrayIterator {

    public function __construct($grp = GRP_NONE, $inverse = false) {
        parent::__construct(grpEditor($grp, $inverse));
    }

    protected function create($key, $value) {
        return new GrpEditor($value);
    }

}
?>
