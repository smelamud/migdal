<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/bug.php');
require_once('lib/tmptexts.php');
require_once('lib/uri.php');
require_once('lib/utils.php');
require_once('lib/sessions.php');
require_once('grp/subdomains.php');

$userRightNames=array('login','hidden','admin_users','admin_topics',
                      'admin_complain_answers','moderator','judge',
		      'admin_domain');
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
		    'IndexPage',
		    'ChatUsersRefresh',
		    'UserPortion');
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
		       'IndexPage'            => 1,
		       'ChatUsersRefresh'     => 60,
		       'UserPortion'          => 30);
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
		     'IndexPage'            => 'ip',
		     'ChatUsersRefresh'     => 'chur',
		     'UserPortion'          => 'up');

function getProperName($name)
{
$parts=explode('_',$name);
$proper='';
foreach($parts as $part)
       $proper.=ucfirst($part);
return $proper;
}

function userRights($aUserId=0)
{
global $sessionid,$userId,$userRightNames,$sessionTimeout,$siteDomain;

settype($sessionid,'integer');

foreach($userRightNames as $name)
       $GLOBALS['user'.getProperName($name)]='';

if(!$sessionid && $aUserId<=0)
  $userId=-1;
else
  if($aUserId<=0)
    {
    $userId=getUserIdBySessionId($sessionid);
    if($userId<=0)
      SetCookie('sessionid',0,0,'/',$siteDomain);
    else
      {
      updateSessionTimestamp($sessionid);
      SetCookie('sessionid',$sessionid,time()+($sessionTimeout+24)*3600,'/',
                $siteDomain);
      }
    }
  else
    $userId=$aUserId;

if($userId>0)
  {
  $rights=mysql_query('select '.join(',',$userRightNames).
		     " from users
		       where id=$userId")
            or sqlbug('������ SQL ��� ��������� ���� ������������');
  $info=mysql_fetch_assoc($rights);
  foreach($info as $name => $value)
	 $GLOBALS['user'.getProperName($name)]=$value;
  if($GLOBALS['userAdminUsers'] && $GLOBALS['userHidden']>0)
    $GLOBALS['userHidden']--;
  updateLastOnline($userId);
  }
}

function userSettings()
{
global $userId,$HTTP_GET_VARS,$HTTP_COOKIE_VARS,
       $userSetNames,$userSetDefaults,$userSetParams,$siteDomain;
       
if($userId>0)
  {
  $dbSettings=getSettingsByUserId($userId);
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
  updateUserSettings($userId,$globs);
if($globs!=$cookieSettings)
  SetCookie('settings',$globs,time()+3600*24*366,'/',$siteDomain);
}

function subDomain()
{
global $forceDomain,$SERVER_NAME,$siteDomain,$userDomain,$REQUEST_URI,
       $subdomains,$userIndexPage;

if($SERVER_NAME=='')
  return;
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
  reload("http://$userDomain.$siteDomain$REQUEST_URI");
}

function session($aUserId=0)
{
userRights($aUserId);
userSettings();
subDomain();
}
?>
