<?php
# @(#) $Id$

require_once('conf/settings.php');

require_once('lib/sessions.php');
require_once('lib/charsets.php');
require_once('lib/post.php');
require_once('lib/time.php');

function getSettingsCookieName($location)
{
return $location==SETL_USER ? 'settings' : 'settings_local';
}

function getSettingsCookie($location)
{
return getSubdomainCookie(getSettingsCookieName($location));
}

function setSettingsCookie($location,$settings)
{
global $siteDomain;

setSubdomainCookie(getSettingsCookieName($location),$settings,
                   time()+3600*24*366,'/',$siteDomain);
}

function getSettingsByUserId($userId)
{
$result=sql("select settings
	     from users
	     where id=$userId",
	    __FUNCTION__);
return mysql_num_rows($result)>0 ? mysql_result($result,0,0) : '';
}

function setSettingsByUserId($userId,$settings)
{
sql("update users
     set settings='$settings'
     where id=$userId",
    __FUNCTION__);
journal("update users
	 set settings='".jencode($settings)."'
	 where id=".journalVar('users',$userId));
}

function settingsDefault()
{
global $allSettings;

foreach($allSettings as $name => $info)
       $GLOBALS['user'.ucfirst($name)]=$info['default'];
}

function getSettingsString($location,$convert)
{
global $allSettings;

$settings=array();
foreach($allSettings as $name => $info)
       {
       if($info['location']!=$location)
         continue;
       $value=$GLOBALS['user'.ucfirst($name)];
       if($convert)
         $value=convertOutputString($value);
       if($info['type']=='string')
         $value=base64_encode($value);
       $settings[]=$info['abbrev']."=$value";
       }
return join(':',$settings);
}

function getSettingsAbbrevMap()
{
global $allSettings,$settingsAbbrevMap;

if(!isset($settingsAbbrevMap))
  {
  $map=array();
  foreach($allSettings as $name => $info)
	 $map[$info['abbrev']]=$name;
  $settingsAbbrevMap=$map;
  }
return $settingsAbbrevMap;
}

function parseSettingsString($settings,$convert)
{
global $allSettings;

$map=getSettingsAbbrevMap();
foreach(explode(':',$settings) as $assign)
       {
       list($abbrev,$value)=explode('=',$assign);
       if(!isset($map[$abbrev]))
         continue;
       $name=$map[$abbrev];
       $info=$allSettings[$name];
       if($info['type']=='string')
         $value=base64_decode($value);
       if($convert)
         {
	 $func='postProcess'.ucfirst($info['type']);
	 if(function_exists($func))
	   $value=$func($value);
	 }
       $GLOBALS['user'.ucfirst($name)]=$value;
       }
}

function updateSettingsCookie($location)
{
$globs=getSettingsString($location,true);
$cookieSettings=getSettingsCookie($location);
if($globs!=$cookieSettings)
  setSettingsCookie($location,$globs);
}

function userSettings()
{
global $userId,$Args;
       
settingsDefault();
if($userId>0)
  parseSettingsString(getSettingsByUserId($userId),false);
parseSettingsString(getSettingsCookie(SETL_USER),true);
parseSettingsString(getSettingsCookie(SETL_HOST),true);

updateSettingsCookie(SETL_USER);

if(isset($Args['print']) && $Args['print']!=0
   || isset($_GET['print']) && $_GET['print']!=0)
  $GLOBALS['userStyle']=-1;
}
?>
