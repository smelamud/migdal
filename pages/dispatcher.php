<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/structure.php');
require_once('lib/post.php');
require_once('lib/database.php');
require_once('lib/redirs.php');
require_once('lib/session.php'); // FIXME Не очень хорошее решение

require_once('conf/subdomains.php');

function &dispatchSubDomain($requestURI)
{
global $siteDomain,$userDomain,$subdomains;

$serverName=$_SERVER['SERVER_NAME'];
if(isset($_SERVER['HTTP_HOST'])
   && strlen($_SERVER['HTTP_HOST'])>strlen($serverName))
  $serverName=$_SERVER['HTTP_HOST'];
if($serverName=='')
  return;
if(strlen($serverName)>strlen($siteDomain))
  $currentDomain=substr(strtolower($serverName),0,
                        strlen($serverName)-strlen($siteDomain)-1);
else
  $currentDomain=strtolower($serverName);
if(in_array($currentDomain,$subdomains))
  $userDomain=$currentDomain;
else
  $userDomain=$subdomains[1];
if($userDomain!=$currentDomain)
  {
  $info=new LocationInfo();
  $info->setPath("http://$userDomain.$siteDomain{$_SERVER['REQUEST_URI']}");
  return $info;
  }
else
  {
  $info=null;
  return $info;
  }
}

function &dispatch404($requestPath)
{
$info=getLocationInfo('/404/',0);
if($info->getScript()=='' && $info->getPath()==$requestPath)
  {
  $info=new LocationInfo();
  $info->setPath($requestPath);
  $info->setScript('404.php');
  }
return $info;
}

function &dispatchScript($requestPath,$parts)
{
$info=new LocationInfo();
$pos=strpos($requestPath,'.php');
$scriptName=substr($requestPath,1,$pos+3);
$trapFunc='trap'.ucfirst(substr(strtr($scriptName,'/-','__'),0,-4));
if(function_exists($trapFunc))
  {
  $path=$trapFunc(parseQuery($parts['query']));
  if($path!='')
    {
    $info->setPath($path);
    return $info;
    }
  else
    return dispatch404($requestPath);
  }
if(!file_exists($scriptName))
  return dispatch404($requestPath);
$info->setPath($requestPath);
$info->setScript($scriptName);
return $info;
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
global $redirid;

if($redirid!=0 && !redirExists($redirid))
  {
  $info=new LocationInfo();
  $info->setPath(remakeURI($_SERVER['REQUEST_URI'],array('redirid')));
  return $info;
  }
$info=getLocationInfo($requestPath,$redirid);
if($info->getScript()=='' && $info->getPath()==$requestPath)
  $info=dispatch404($requestPath);
return $info;
}

function &dispatch()
{
$info=dispatchSubDomain($_SERVER['REQUEST_URI']);
if(!is_null($info))
  return $info;
$parts=parse_url($_SERVER['REQUEST_URI']);
$requestPath=$parts['path'];
if($requestPath=='/dispatcher.php')
  return dispatch404($requestPath);
elseif(strpos($requestPath,'.php')!==false)
  return dispatchScript($requestPath,$parts);
elseif(substr($requestPath,0,9)!='/actions/' && substr($requestPath,-1)!='/'
       && substr($requestPath,5)!='.html')
  return dispatchAddSlash($requestPath,$parts);
else
  return dispatchLocation($requestPath);
}

function exposeArgs($args)
{
foreach($args as $name => $value)
       postValue($name,$value);
}

postInteger('redirid');
unset($Args['redirid']);

dbOpen();
session(); // FIXME Не очень хорошее решение для того, чтобы можно было в
           // structure.conf проверять права юзера
$LocationInfo=&dispatch();
$ScriptName=$LocationInfo->getScript();
if($ScriptName!='')
  {
  exposeArgs($LocationInfo->getArgs());
  redirect();
  include($ScriptName);
  }
else
  header('Location: '.$LocationInfo->getPath());
dbClose();
?>
