<?php
# @(#) $Id$

function makeQuery($vars,$remove=array(),$subs=array())
{
$s='';
foreach(count($subs)!=0 ? array_merge($vars,$subs) : $vars as $key => $value)
       if(!in_array($key,$remove) && "$value"!='')
         if(!is_array($value))
           $s.=($s!='' ? '&' : '')."$key=".urlencode($value);
         else
	   foreach($value as $elm)
                  $s.=($s!='' ? '&' : '')."${key}[]=".urlencode($elm);
return $s;
}

function parseQuery($query)
{
$asses=explode('&',$query);
$vars=array();
foreach($asses as $ass) 
       { 
       list($key,$value)=explode('=',$ass); 
       if(substr($key,-2)!='[]')
         $vars[$key]=urldecode($value);
       else
         {
	 $key=substr($key,0,strlen($key)-2);
	 if(!is_array($vars[$key]))
	   $vars[$key]=array();
	 $vars[$key][]=urldecode($value);
	 }
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

function remakeMakeQuery($query,$vars,$remove=array(),$subs=array())
{
$all=parseQuery($query);
$all=array_merge($all,$vars);
return makeQuery($all,$remove,$subs);
}

function remakeMakeURI($uri,$vars,$remove=array(),$subs=array(),$location='#')
{
list($start,$end)=explode('?',$uri);
list($query,$end)=explode('#',$end);
$end=$location=='#' ? $end : $location;
return "$start?".remakeMakeQuery($query,$vars,$remove,$subs)
                .($end!='' ? "#$end" : '');
}

class HTTPVar
{
var $name;
var $value;

function HTTPVar($name,$value)
{
$this->name=$name;
$this->value=$value;
}

function getName()
{
return $this->name;
}

function getValue()
{
return $this->value;
}

}

class HTTPVarsIterator
{
var $vars;
var $rm;

function HTTPVarsIterator($vars,$rm='')
{
$this->vars=$vars;
if(is_array($rm))
  $this->rm=$rm;
else
  $this->rm=array($rm);
reset($this->vars);
}

function next()
{
do
  {
  $cur=each($this->vars);
  }
while($cur && in_array($cur[0],$this->rm));
return $cur ? new HTTPVar($cur[0],$cur[1]) : 0;
}

}
?>
