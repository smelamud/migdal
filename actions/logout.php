<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/uri.php');
require_once('lib/post.php');
require_once('lib/random.php');
require_once('lib/errors.php');
require_once('lib/logs.php');

function logout($sessionid)
{
global $siteDomain;

$result=mysql_query("select user_id,real_user_id
                     from sessions
		     where sid=$sessionid");
if(!$result)
  return ELO_SQL_GET;
if(mysql_num_rows($result)>0)
  {
  $row=mysql_fetch_assoc($result);
  logEvent('logout','user('.$row['user_id'].')');
  if($row['user_id']!=$row['real_user_id'])
    {
    $result=mysql_query("update sessions
                         set user_id=real_user_id
			 where sid=$sessionid");
    if(!$result)
      return ELO_SQL_SWITCH;
    return ELO_OK;
    }
  }
$result=mysql_query("delete from sessions
		     where sid=$sessionid");
if(!$result)
  return ELO_SQL_DROP;
SetCookie('sessionid',0,0,'/',$siteDomain);
return ELO_OK;
}

settype($sessionid,'integer');

dbOpen();
$err=logout($sessionid);
if($err==ELO_OK)
  header('Location: '.remakeURI($okdir,
				array(),
				array('reload' => random(0,999))));
else
  header('Location: '.remakeURI($faildir,
				array(),
				array('err' => $err)));
dbClose();
?>
