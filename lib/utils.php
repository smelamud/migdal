<?php
# @(#) $Id$

function makeQuery($vars,$remove=array(),$subs=array())
{
$s='';
foreach(array_merge($vars,$subs) as $key => $value)
       if(!in_array($key,$remove) && $value)
         $s.=($s!='' ? '&' : '')."$key=".urlencode($value);
return $s;
}

function makeValue($value)
{
$c=is_int($value) ? '' : '"';
return $c.AddSlashes($value).$c;
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
