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
if(!is_null($value))
  {
  $c=is_numeric($value) ? '' : '"';
  return $c.addslashes($value).$c;
  }
else
  return 'null';
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

function disjunct($values)
{
if(!is_array($values))
  return $values;
$sum=0;
foreach($values as $value)
       $sum|=$value;
return $sum;
}

define('SLASH_ANY',0);
define('SLASH_YES',1);
define('SLASH_NO',-1);

function normalizePath($path,$singleSlash=false,$firstSlash=SLASH_ANY,
                       $lastSlash=SLASH_ANY)
{
if($path=='')
  return $firstSlash==SLASH_YES || $lastSlash==SLASH_YES ? '/' : '';
if($singleSlash)
  $path=preg_replace('/\/+/','/',$path);
if($firstSlash==SLASH_YES)
  if($path{0}!='/')
    $path="/$path";
if($firstSlash==SLASH_NO)
  if($path{0}=='/')
    $path=substr($path,1);
if($lastSlash==SLASH_YES)
  if($path{strlen($path)-1}!='/')
    $path.='/';
if($lastSlash==SLASH_NO)
  if($path{strlen($path)-1}=='/')
    $path=substr($path,0,-1);
return $path;
}
?>
