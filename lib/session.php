<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/tmptexts.php');
require_once('lib/uri.php');
require_once('lib/utils.php');
require_once('grp/subdomains.php');

$userRightNames=array('login','hidden','admin_users','admin_topics',
                      'admin_complain_answers','moderator','judge','admin_domain');
$userSetNames=array('MsgPortion',
                    'ForumPortion',
                    'ComplainPortion',
	            'ChatPortion',
	            'ChatRefresh',
		    'ForumCatalogPortion',
		    'Style',
		    'PictureRowPortion',
		    'PictureColumnPortion',
		    'BiffTime',
		    'ReadKOI',
		    'IndexPage');
$userSetDefaults=array('MsgPortion'           => 10,
                       'ForumPortion'         => 10,
                       'ComplainPortion'      => 20,
	               'ChatPortion'          => 20,
	               'ChatRefresh'          => 10,
		       'ForumCatalogPortion'  => 20,
		       'Style'                => 1,
		       'PictureRowPortion'    => 4,
		       'PictureColumnPortion' => 5,
		       'BiffTime'             => 24,
		       'ReadKOI'              => 0,
		       'IndexPage'            => 1);
$userSetParams=array('MsgPortion'           => 'mp',
                     'ForumPortion'         => 'fp',
		     'ComplainPortion'      => 'cp',
		     'ChatPortion'          => 'chp',
		     'ChatRefresh'          => 'chr',
		     'ForumCatalogPortion'  => 'fcp',
		     'Style'                => 'st',
		     'PictureRowPortion'    => 'prp',
		     'PictureColumnPortion' => 'pcp',
		     'BiffTime'             => 'bt',
		     'ReadKOI'              => 'rk',
		     'IndexPage'            => 'ip');

function getProperName($name)
{
$parts=explode('_',$name);
$proper='';
foreach($parts as $part)
       $proper.=ucfirst($part);
return $proper;
}

function userRights()
{
global $sessionid,$userId,$userRightNames,$sessionTimeout,$siteDomain;

settype($sessionid,'integer');

foreach($userRightNames as $name)
       $GLOBALS['user'.getProperName($name)]='';

if(!$sessionid)
  $userId=-1;
else
  {
  $result=mysql_query("select user_id from sessions where sid=$sessionid")
	       or die('Ошибка SQL при выборке сессии');
  if(mysql_num_rows($result)<=0)
    {
    SetCookie('sessionid',0,0,'/',$siteDomain);
    $userId=-1;
    }
  else
    {
    $userId=mysql_result($result,0,0);
    $rights=mysql_query('select '.join(',',$userRightNames).
		       " from users
			 where id=$userId")
	  or die('Ошибка SQL при получении прав пользователя');
    $info=mysql_fetch_assoc($rights);
    foreach($info as $name => $value)
           $GLOBALS['user'.getProperName($name)]=$value;
    if($GLOBALS['userAdminUsers'] && $GLOBALS['userHidden']>0)
      $GLOBALS['userHidden']--;
    mysql_query("update sessions set last=null where sid=$sessionid")
	 or die('Ошибка SQL при обновлении TIMESTAMP сессии');
    mysql_query("update users set last_online=now() where id=$userId")
	 or die('Ошибка SQL при обновлении времени захода пользователя');
    SetCookie('sessionid',$sessionid,time()+($sessionTimeout+24)*3600,'/',$siteDomain);
    }
  }
}

function userSettings()
{
global $userId,$HTTP_GET_VARS,$HTTP_COOKIE_VARS,
       $userSetNames,$userSetDefaults,$userSetParams,$siteDomain;
       
if($userId>0)
  {
  $result=mysql_query("select settings
                       from users
	               where id=$userId")
	       or die('Ошибка SQL при выборке установок пользователя');
  $dbSettings=mysql_result($result,0,0);
  $row=explode(':',$dbSettings);
  }
else
  $row=array();
$cookieSettings=$HTTP_COOKIE_VARS['settings'];
$cookie=explode(':',$cookieSettings);
foreach($userSetNames as $i => $name)
       {
       $row[$name]=$row[$i];
       $cookie[$name]=$cookie[$i];
       }
$update=array();
foreach($userSetNames as $name)
       {
       $par=$HTTP_GET_VARS[$userSetParams[$name]];
       $cook=$cookie[$name];
       $db=$row[$name];
       settype($par,'integer');
       settype($cook,'integer');
       $glob=!empty($par) ? $par :
            (!empty($db) ? $db :
            (!empty($cook) ? $cook : $userSetDefaults[$name]));
       $update[]=$glob;
       $GLOBALS["user$name"]=$glob;
       }
$globs=join(':',$update);
if($userId>0 && $globs!=$dbSettings)
  mysql_query("update users
               set settings='$globs'
	       where id=$userId")
       or die('Ошибка SQL при сохранении установок пользователя');
if($globs!=$cookieSettings)
  SetCookie('settings',$globs,time()+3600*24*366,'/',$siteDomain);
}

function subDomain()
{
global $forceDomain,$SERVER_NAME,$siteDomain,$userDomain,$REQUEST_URI,$subdomains,
       $userIndexPage;

if(strlen($SERVER_NAME)>strlen($siteDomain))
  $currentDomain=substr(strtolower($SERVER_NAME),0,
                        strlen($SERVER_NAME)-strlen($siteDomain)-1);
else
  $currentDomain=strtolower($SERVER_NAME);
if($forceDomain!='')
  $userDomain=$forceDomain;
else
  if(in_array($currentDomain,$subdomains))
    $userDomain=$currentDomain;
  else
    if(!empty($subdomains[$userIndexPage]))
      $userDomain=$subdomains[$userIndexPage];
    else
      $userDomain=$subdomains[1];
if($userDomain!=$currentDomain)
  {
  header("Location: http://$userDomain.$siteDomain$REQUEST_URI");
  exit;
  }
}

function session()
{
userRights();
userSettings();
subDomain();
}
?>
