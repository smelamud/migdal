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

function uc($s)
{
setlocale('LC_CTYPE','ru_RU.KOI8-R');
return strtoupper($s);
}

function unhtmlentities($s)
{
return strtr($s,
             array_flip(get_html_translation_table(HTML_ENTITIES,ENT_QUOTES)));
}

function getPlural($n,$forms)
{
$a=$n%10;
$b=((int)$n/10)%10;
return $b==1 || $a>=5 || $a==0 ? $forms[2] : ($a==1 ? $forms[0] : $forms[1]);
}

function getQuote($s,$width)
{
return preg_replace('/^/m','> ',wordwrap($s,$width));
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
