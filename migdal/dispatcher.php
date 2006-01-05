<?php
# @(#) $Id$

require_once('lib/structure.php');
require_once('lib/post.php');
require_once('lib/database.php');

function &dispatch404($requestPath)
{
$info=new LocationInfo();
$info->setPath($requestPath);
$info->setScript('404.php');
return $info;
}

function &dispatchScript($requestPath)
{
$info=new LocationInfo();
$scriptName=substr($requestPath,1);
if(!file_exists($scriptName))
  return dispatch404($requestPath);
else
  {
  $info->setPath($requestPath);
  $info->setScript($scriptName);
  return $info;
  }
}

function &dispatchAddSlash($requestPath,$parts)
{
$href="$requestPath/";
if(isset($parts['query']) && $parts['query']!='')
  $href.='?'.$parts['query'];
if(isset($parts['fragment']) && $parts['fragment']!='')
  $href.='#'.$parts['fragment'];
$info=new LocationInfo();
$info->setPath($href);
return $info;
}

function &dispatchLocation($requestPath)
{
dbOpen();
return getLocationInfo($requestPath);
}

function &dispatch()
{
$parts=parse_url($_SERVER['REQUEST_URI']);
$requestPath=$parts['path'];
if($requestPath=='/dispatcher.php')
  return dispatch404($requestPath);
elseif(substr($requestPath,-4)=='.php')
  return dispatchScript($requestPath);
elseif(substr($requestPath,0,9)!='/actions/' && substr($requestPath,-1)!='/'
       && substr($requestPath,5)!='.html')
  return dispatchAddSlash($requestPath,$parts);
else
  return dispatchLocation($requestPath);
}

function exposeArgs($args)
{
foreach($args as $key => $value)
       {
       list($name,$type)=split(':',$key);
       if($type=='')
	 $type='string';
       $func='post'.ucfirst($type).'Value';
       $func($name,$value);
       }
}

$LocationInfo=&dispatch();
$ScriptName=$LocationInfo->getScript();
if($ScriptName!='')
  {
  exposeArgs($LocationInfo->getArgs());
  include($ScriptName);
  }
else
  header('Location: '.$LocationInfo->getPath());
?>
