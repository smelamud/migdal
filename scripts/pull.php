<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/postings.php');
require_once('lib/post.php');
require_once('lib/sql.php');

function pull($postid)
{
global $rootPostingPerms;

$msgid=getMessageIdByPostingId($postid);
sql("update messages
     set sent=now(),perms=$rootPostingPerms
     where id=$msgid",
    'pull');
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
