<?php
# @(#) $Id$

function makeQuery($vars,$remove=array(),$subs=array())
{
$s='';
foreach(count($subs)!=0 ? array_merge($vars,$subs) : $vars as $key => $value)
       if(!in_array($key,$remove) && "$value"!='')
         $s.=($s!='' ? '&' : '')."$key=".urlencode($value);
return $s;
}

function parseQuery($query)
{
$asses=explode('&',$query);
$vars=array();
foreach($asses as $ass) 
       { 
       list($key,$value)=explode('=',$ass); 
       $vars[$key]=urldecode($value);
       }
return $vars;
}

function remakeQuery($query,$remove=array(),$subs=array())
{
return makeQuery(parseQuery($query),$remove,$subs);
}

function remakeURI($uri,$remove=array(),$subs=array(),$location='#')
{
list($start,$end)=explode('?',$uri);
list($query,$end)=explode('#',$end);
$end=$location=='#' ? $end : $location;
return "$start?".remakeQuery($query,$remove,$subs).($end!='' ? "#$end" : '');
}

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
?>
