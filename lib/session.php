<?php
# @(#) $Id$

$userSetDBNames=array('mp' => 'msg_portion',
                      'fp' => 'forum_portion');
$userSetGlobalNames=array('mp' => 'MsgPortion',
                          'fp' => 'ForumPortion');
$userSetDefaults=array('mp' => 10,
                       'fp' => 10);

function userSettings()
{
global $userId,$HTTP_GET_VARS,$HTTP_COOKIE_VARS,
       $userSetDBNames,$userSetGlobalNames,$userSetDefaults;
       
if($userId>0)
  {
  $result=mysql_query('select '.join(',',$userSetDBNames).
                     " from users
		       where id=$userId")
	       or die('Ошибка SQL при выборке установок пользователя');
  $row=mysql_fetch_assoc($result);
  }
else
  $row=array();
$update=array();
foreach($userSetGlobalNames as $key => $name)
       {
       $par=$HTTP_GET_VARS[$key];
       $cookie=$HTTP_COOKIE_VARS["cookie$key"];
       $db=$row[$userSetDBNames[$key]];
       settype($par,'integer');
       settype($cookie,'integer');
       $glob=!empty($par) ? $par :
            (!empty($cookie) ? $cookie :
            (!empty($db) ? $db : $userSetDefaults[$key]));
       if($userId>0 && $db!=$glob)
         $update[$userSetDBNames[$key]]=$glob;
       if($cookie!=$glob)
         SetCookie("cookie$key",$glob,time()+3600*24*366);
       $GLOBALS["user$name"]=$glob;
       }
if($userId>0 && count($update)!=0)
  mysql_query('update users
               set '.makeKeyValue(',',$update).
	     " where id=$userId")
       or die('Ошибка SQL при сохранении установок пользователя');
}

function session()
{
global $sessionid,
       $userId,$userAdminUsers,$userAdminTopics,$userModerator;

settype($sessionid,'integer');

if(!$sessionid)
  $userId=-1;
else
  {
  $result=mysql_query("select user_id from sessions where sid=$sessionid")
	       or die('Ошибка SQL при выборке сессии');
  if(mysql_num_rows($result)<=0)
    {
    SetCookie('sessionid');
    $userId=-1;
    }
  else
    {
    $userId=mysql_result($result,0,0);
    $rights=mysql_query("select admin_users,admin_topics,moderator
			 from users
			 where id=$userId")
		 or die('Ошибка SQL при получении прав пользователя');
    list($userAdminUsers,$userAdminTopics,
         $userModerator)=mysql_fetch_row($rights);
    mysql_query("update sessions set last=null where sid=$sessionid")
	 or die('Ошибка SQL при обновлении TIMESTAMP сессии');
    SetCookie('sessionid',$sessionid,time()+7200);
    }
  }
userSettings();
}
?>
