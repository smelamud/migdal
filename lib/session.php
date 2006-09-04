<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/bug.php');
require_once('lib/tmptexts.php');
require_once('lib/uri.php');
require_once('lib/utils.php');
require_once('lib/sessions.php');
require_once('lib/users.php');
require_once('lib/sql.php');
require_once('lib/ctypes.php');

$userRights=array('AdminUsers'           => USR_ADMIN_USERS,
                  'AdminTopics'          => USR_ADMIN_TOPICS,
                  'AdminComplainAnswers' => USR_ADMIN_COMPLAIN_ANSWERS,
		  'Moderator'            => USR_MODERATOR,
		  'Judge'                => USR_JUDGE,
		  'AdminDomain'          => USR_ADMIN_DOMAIN);
$userSetNames=array('MsgPortion', // obsolete
                    'ForumPortion',
                    'ComplainPortion', // obsolete
	            'ChatPortion',
	            'ChatRefresh',
		    'ForumCatalogPortion', // obsolete
		    'Style',
		    'PictureRowPortion',
		    'PictureColumnPortion',
		    'BiffTime',
		    'ReadKOI',
		    'IndexPage', // obsolete
		    'ChatUsersRefresh',
		    'UserPortion'); // obsolete
$userSetDefaults=array('MsgPortion'           => 10, // obsolete
                       'ForumPortion'         => 10,
                       'ComplainPortion'      => 20, // obsolete
	               'ChatPortion'          => 20,
	               'ChatRefresh'          => 10,
		       'ForumCatalogPortion'  => 20, // obsolete
		       'Style'                => 1,
		       'PictureRowPortion'    => 4,
		       'PictureColumnPortion' => 5,
		       'BiffTime'             => 24,
		       'ReadKOI'              => 0,
		       'IndexPage'            => 1, // obsolete
		       'ChatUsersRefresh'     => 60,
		       'UserPortion'          => 30); // obsolete
$userSetParams=array('MsgPortion'           => 'mp', // obsolete
                     'ForumPortion'         => 'fp',
		     'ComplainPortion'      => 'cp', // obsolete
		     'ChatPortion'          => 'chp',
		     'ChatRefresh'          => 'chr',
		     'ForumCatalogPortion'  => 'fcp', // obsolete
		     'Style'                => 'st',
		     'PictureRowPortion'    => 'prp',
		     'PictureColumnPortion' => 'pcp',
		     'BiffTime'             => 'bt',
		     'ReadKOI'              => 'rk',
		     'IndexPage'            => 'ip', // obsolete
		     'ChatUsersRefresh'     => 'chur',
		     'UserPortion'          => 'up'); // obsolete

function sessionGuest()
{
global $userId,$realUserId,$sessionid;

$userId=$realUserId=0;
$sessionid=createSession(0,0);
setSessionCookie($sessionid);
}

function clearUserRights()
{
global $userRights;

$GLOBALS['userLogin']='';
$GLOBALS['userHidden']='';
foreach($userRights as $name => $code)
       $GLOBALS["user$name"]='';
}

function userRights($aUserId=-1)
{
global $sessionid,$userId,$realUserId,$userGroups,$userRights;

$sessionid=getSessionCookie();
$globalsid=$_REQUEST['globalsid'];

clearUserRights();

if($globalsid!=0)
  $sessionid=$globalsid;

if(!$sessionid && $aUserId<0)
  sessionGuest();
elseif($aUserId==0)
  {
  $userId=0;
  $realUserId=getGuestId();
  }
elseif($aUserId<0)
  {
  $row=getUserIdsBySessionId($sessionid);
  if(!$row)
    sessionGuest();
  else
    {
    list($userId,$realUserId)=$row;
    if($userId<=0 && $realUserId<=0)
      {
      $userId=0;
      $realUserId=getGuestId();
      updateSession($sessionid,0,$realUserId);
      }
    else
      {
      updateSessionTimestamp($sessionid);
      setSessionCookie($sessionid);
      }
    }
  }
else
  $userId=$realUserId=$aUserId;

$userGroups=array();
if($userId>0)
  {
  $result=sql("select group_id
	       from groups
	       where user_id=$userId",
	      __FUNCTION__,'get_groups');
  while(list($group_id)=mysql_fetch_array($result))
       $userGroups[]=$group_id;
  }
else
  if($realUserId>0)
    $GLOBALS['userLogin']=getUserLoginById($realUserId);

if($userId>0)
  {
  $rights=sql("select login,hidden,rights
	       from users
	       where id=$userId",
	      __FUNCTION__,'get_rights');
  $info=mysql_fetch_assoc($rights);
  $GLOBALS['userLogin']=$info['login'];
  $GLOBALS['userHidden']=$info['hidden'];
  foreach($userRights as $name => $code)
	 $GLOBALS["user$name"]=($info['rights'] & $code)!=0;
  if($GLOBALS['userAdminUsers'] && $GLOBALS['userHidden']>0)
    $GLOBALS['userHidden']--;
  updateLastOnline($userId);
  }

if($realUserId>0)
  updateLastOnline($realUserId);

if($GLOBALS['userLogin']!='' && c_ascii($GLOBALS['userLogin']))
  $GLOBALS['userFolder']=$GLOBALS['userLogin'];
else
  $GLOBALS['userFolder']=$userId;
}

function userSettings()
{
global $userId,$userSetNames,$userSetDefaults,$userSetParams,$siteDomain,$Args;
       
if($userId>0)
  {
  $dbSettings=getSettingsByUserId($userId);
  $row=explode(':',$dbSettings);
  }
else
  $row=array();
$cookieSettings=getSettingsCookie();
$cookie=explode(':',$cookieSettings);
foreach($userSetNames as $i => $name)
       {
       $row[$name]=$row[$i];
       $cookie[$name]=$cookie[$i];
       }
$update=array();
foreach($userSetNames as $name)
       {
       $par=$_GET[$userSetParams[$name]];
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
if(isset($Args['print']) && $Args['print']!=0 || $_GET['print']!=0)
  $GLOBALS['userStyle']=-1;
$globs=join(':',$update);
if($userId>0 && $globs!=$dbSettings)
  updateUserSettings($userId,$globs);
if($globs!=$cookieSettings)
  setSettingsCookie($globs);
}

function session($aUserId=-1)
{
userRights($aUserId);
userSettings();
}
?>
