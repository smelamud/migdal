<?php
# @(#) $Id$

require_once('lib/structure.php');
require_once('lib/post.php');
require_once('lib/database.php');

$href=$_SERVER['REQUEST_URI'];
$parts=parse_url($href);
$RequestPath=$parts['path'];
$ScriptName='';
if($RequestPath=='/dispatcher.php')
  $ScriptName='404.php';
elseif(substr($RequestPath,-4)=='.php')
  {
  $ScriptName=substr($RequestPath,1);
  if(!file_exists($ScriptName))
    $ScriptName='404.php';
  }
elseif(substr($RequestPath,-1)!='/' && substr($RequestPath,5)!='.html')
  {
  $href="$RequestPath/";
  if(isset($parts['query']) && $parts['query']!='')
    $href.='?'.$parts['query'];
  if(isset($parts['fragment']) && $parts['fragment']!='')
    $href.='#'.$parts['fragment'];
  header("Location: $href");
  }
else
  {
  //TODO it's possible to get backtrace here
  dbOpen();
  $info=getLocationInfo($RequestPath);
  $ScriptName=$info->getScript();
  foreach($info->getArgs() as $key => $value)
         {
	 list($name,$type)=split(':',$key);
	 if($type=='')
	   $type='string';
	 $func='post'.ucfirst($type).'Value';
	 $func($name,$value);
	 }
  unset($func);
  unset($name);
  unset($type);
  unset($info);
  }
unset($parts);
unset($href);
if($ScriptName!='')
  include($ScriptName);
?>
