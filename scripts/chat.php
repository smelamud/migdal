<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/users.php');
require_once('lib/chat-users.php');
require_once('lib/chat.php');
require_once('lib/sql.php');

function cleanup()
{
global $chatTimeout;

$result=sql("select id
	     from users
	     where last_chat+interval $chatTimeout minute<now()
		   and in_chat<>0",
	    'cleanup');
while($row=mysql_fetch_assoc($result)) 
     {
     postChatLogoutMessage($row['id']);
     chatLogout($row['id']);
     }
}

dbOpen();
session(getShamesId());
cleanup();
dbClose();
?>
