<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/utils.php');

$userRightNames=array('login','hidden','admin_users','admin_topics',
                      'moderator','judge');
$userSetNames=array('mp' => 'msg_portion',
                    'fp' => 'forum_portion',
                    'cp' => 'complain_portion');
$userSetDefaults=array('mp' => 10,
                       'fp' => 10,
		       'cp' => 20);

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
global $sessionid,$userId,$userRightNames;

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
    SetCookie('sessionid',0,0,'/');
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
    if($GLOBALS['userAdminUsers'])
      $GLOBALS['userHidden']--;
    mysql_query("update sessions set last=null where sid=$sessionid")
	 or die('Ошибка SQL при обновлении TIMESTAMP сессии');
    SetCookie('sessionid',$sessionid,time()+($sessionTimeout+1)*3600,'/');
    }
  }
}

function userSettings()
{
global $userId,$HTTP_GET_VARS,$HTTP_COOKIE_VARS,
       $userSetNames,$userSetDefaults;
       
if($userId>0)
  {
  $result=mysql_query('select '.join(',',$userSetNames).
                     " from users
		       where id=$userId")
	       or die('Ошибка SQL при выборке установок пользователя');
  $row=mysql_fetch_assoc($result);
  }
else
  $row=array();
$update=array();
foreach($userSetNames as $key => $name)
       {
       $par=$HTTP_GET_VARS[$key];
       $cookie=$HTTP_COOKIE_VARS["cookie$key"];
       $db=$row[$name];
       settype($par,'integer');
       settype($cookie,'integer');
       $glob=!empty($par) ? $par :
            (!empty($cookie) ? $cookie :
            (!empty($db) ? $db : $userSetDefaults[$key]));
       if($userId>0 && $db!=$glob)
         $update[$name]=$glob;
       if($cookie!=$glob)
         SetCookie("cookie$key",$glob,time()+3600*24*366,'/');
       $GLOBALS['user'.getProperName($name)]=$glob;
       }
if($userId>0 && count($update)!=0)
  mysql_query('update users
               set '.makeKeyValue(',',$update).
	     " where id=$userId")
       or die('Ошибка SQL при сохранении установок пользователя');
}

function session()
{
userRights();
userSettings();
}
?>
