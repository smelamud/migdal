<?php
# @(#) $Id$

require_once('lib/uri.php');

function reloadParameter($cond,$key,$value)
{
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

function subParams($text,$params)
{
return preg_replace('/%(\w+)/e','$params[$1]',$text);
}
?>
