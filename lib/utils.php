<?php
# @(#) $Id$

require_once('lib/uri.php');
require_once('lib/database.php');

function reload($href)
{
header("Location: $href");
dbClose();
exit;
}

function makeValue($value)
{
$c=is_int($value) ? '' : '"';
return $c.addslashes($value).$c;
}

function makeKeyValue($join,$list)
{
$s='';
foreach($list as $key=>$value)
       $s.=($s!='' ? $join : '')."$key=".makeValue($value);
return $s;
}

function makeValueList($join,$list)
{
$s='';
foreach($list as $value)
       $s.=($s!='' ? $join : '').makeValue($value);
return $s;
}

function makeInsert($table,$what)
{
return "insert into $table(".join(',',array_keys($what)).
            ') values ('.makeValueList(',',$what).')';
}

function makeUpdate($table,$what,$where)
{
return "update $table set ".makeKeyValue(',',$what).
                    ' where '.makeKeyValue(' and ',$where);
}

function displayImage($src='',$alt='',$title='',$border='')
{
static $sizes=array();

$s='<img';
if($src!='')
  {
  $s.=" src='$src' ";
  if(isset($sizes[$src]))
    $s.=$sizes[$src];
  else
    {
    $is=getImageSize("./$src");
    $s.=$is[3];
    $sizes[$src]=$is[3];
    }
  }
if($alt!='')
  $s.=" alt='$alt'";
if($title!='')
  $s.=" title='$title'";
if($border!='')
  $s.=" border='$border'";
echo "$s>";
}
?>
