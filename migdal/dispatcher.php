<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/structure.php');
require_once('lib/post.php');
require_once('lib/database.php');
require_once('lib/redirs.php');

require_once('grp/subdomains.php');

function &dispatchSubDomain($requestURI)
{
global $siteDomain,$userDomain,$subdomains;

if($_SERVER['SERVER_NAME']=='')
  return;
if(strlen($_SERVER['SERVER_NAME'])>strlen($siteDomain))
  $currentDomain=substr(strtolower($_SERVER['SERVER_NAME']),0,
                        strlen($_SERVER['SERVER_NAME'])-strlen($siteDomain)-1);
else
  $currentDomain=strtolower($_SERVER['SERVER_NAME']);
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
  return null;
}

function &dispatch404($requestPath)
{
$info=new LocationInfo();
$info->setPath($requestPath);
$info->setScript('404.php');
return $info;
}

function &dispatchScript($requestPath,$parts)
{
$info=new LocationInfo();
$scriptName=substr($requestPath,1);
$oldRedirectFunc='oldRedirect'.ucfirst(substr($scriptName,0,-4));
if(function_exists($oldRedirectFunc))
  {
  $path=$oldRedirectFunc(parseQuery($parts['query']));
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
elseif(substr($requestPath,-4)=='.php')
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
