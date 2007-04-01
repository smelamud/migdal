<?php
# @(#) $Id$

function makeQuery($vars,$remove=array(),$subs=array())
{
// Кодирование происходит внутри - передавайте раскодированные параметры
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
       if(strpos($ass,'=')!==false)
         list($key,$value)=explode('=',$ass);
       else
         {
	 $key=$ass;
	 $value='';
	 }
       $value=urldecode($value);
       if(substr($key,-2)!='[]')
         $vars[$key]=$value;
       else
         {
	 $key=substr($key,0,strlen($key)-2);
	 if(!isset($vars[$key]) || !is_array($vars[$key]))
	   $vars[$key]=array();
	 $vars[$key][]=$value;
	 }
       }
return $vars;
}

function remakeQuery($query,$remove=array(),$subs=array())
{
return makeQuery(parseQuery($query),$remove,$subs);
}

function parseURI($uri)
{
if(strpos($uri,'#')!==false)
  list($start,$end)=explode('#',$uri);
else
  {
  $start=$uri;
  $end='';
  }
if(strpos($start,'?')!==false)
  list($start,$query)=explode('?',$start);
else
  $query='';
return array($start,$query,$end);
}

function remakeURI($uri,$remove=array(),$subs=array(),$location='#')
{
list($start,$query,$end)=parseURI($uri);
$query=remakeQuery($query,$remove,$subs);
$end=$location=='#' ? $end : $location;
return $start.($query!='' ? "?$query" : '').($end!='' ? "#$end" : '');
}

function remakeMakeQuery($query,$vars,$remove=array(),$subs=array())
{
$all=parseQuery($query);
$all=array_merge($all,$vars);
return makeQuery($all,$remove,$subs);
}

function remakeMakeURI($uri,$vars,$remove=array(),$subs=array(),$location='#')
{
list($start,$query,$end)=parseURI($uri);
$query=remakeMakeQuery($query,$vars,$remove,$subs);
$end=$location=='#' ? $end : $location;
return $start.($query!='' ? "?$query" : '').($end!='' ? "#$end" : '');
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

function getURLPath($url)
{
$parts=parse_url($url);
return $parts['path'];
}

function getURLDomain($url)
{
$parts=parse_url($url);
$host=strtolower($parts['host']);
if(substr($host,0,4)=='www.')
  $host=substr($host,4);
return $host;
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
