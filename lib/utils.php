<?php
# @(#) $Id$

require_once('lib/uri.php');

function reloadParameter($cond,$key,$value)
{
global $REQUEST_URI;

if($cond)
  {
  header('Location: '.remakeURI($REQUEST_URI,
                                array(),
				array($key => $value)));
  exit;
  }
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

function uc($s)
{
return strtr(strtoupper($s),'ÁÂ×ÇÄÅÖÚÉÊËÌÍÎÏÐÒÓÔÕÆÈÃÞÛÝßÙØÜÀÑ',
                            'áâ÷çäåöúéêëìíîïðòóôõæèãþûýÿùøüàñ');
}

function getPlural($n,$forms)
{
$a=$n%10;
$b=((int)$n/10)%10;
return $b==1 || $a>=5 || $a==0 ? $forms[2] : ($a==1 ? $forms[0] : $forms[1]);
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
    $is=getImageSize($src);
    $s.=$is[3];
    $sizes[$src]=$is[3];
    }
  }
if($alt!='')
  $s.=" alt='$alt'";
if($title!='')
  $s.=" title='$title'";
if($border!='')
  $s.=" border=$border";
echo "$s>";
}
?>
