<?php
# @(#) $Id$

function session()
{
global $sessionid,$cookiemp,$mp,
       $userId,$userAdminUsers,$userAdminTopics,$userModerator,
       $userMsgPortion;

settype($sessionid,'integer');
settype($cookiemp,'integer');
settype($mp,'integer');

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
    $rights=mysql_query("select admin_users,admin_topics,moderator,msg_portion
			 from users
			 where id=$userId")
		 or die('Ошибка SQL при получении прав пользователя');
    list($userAdminUsers,$userAdminTopics,$userModerator,
	 $dbMP)=mysql_fetch_row($rights);
    mysql_query("update sessions set last=null where sid=$sessionid")
	 or die('Ошибка SQL при обновлении TIMESTAMP сессии');
    SetCookie('sessionid',$sessionid,time()+7200);
    }
  }
$userMsgPortion=!empty($mp) ? $mp :
                (!empty($cookiemp) ? $cookiemp :
                (!empty($dbMP) ? $dbMP : 10));
if($userId>0 && $dbMP!=$userMsgPortion)
  mysql_query("update users set msg_portion=$userMsgPortion where id=$userId")
       or die('Ошибка SQL при сохранении установок пользователя');
if($cookiemp!=$userMsgPortion)
  SetCookie('cookiemp',$userMsgPortion,time()+3600*24*366);
}
?>
