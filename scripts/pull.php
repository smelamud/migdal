<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/postings.php');
require_once('lib/post.php');

function pull($postid)
{
global $rootPostingPerms;

$msgid=getMessageIdByPostingId($postid);
mysql_query("update messages
             set sent=now(),perms=$rootPostingPerms
	     where id=$msgid")
     or die('Ошибка SQL при выкладке постинга');
journal("update messages
         set sent=now(),perms=$rootPostingPerms
	 where id=".journalVar('messages',$msgid));
}

commandLineArgs();

dbOpen();
session();
pull($args[0]);
dbClose();
?>
