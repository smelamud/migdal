<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/users.php');
require_once('lib/exec.php');
require_once('lib/sql.php');

function initCheckTimes()
{
sql('update messages
     set url_check=sent,url_check_success=sent
     where url_check=0 or url_check_success=0',
    'initCheckTimes');
}

function checkURLs()
{
global $urlCheckTimeout,$urlCheckQuota,$wgetPath;

$now=sqlNow();
$result=sql("select id,url
	     from messages
	     where url like '%://%' and
		   url_check+interval $urlCheckTimeout day<'$now'
	     order by url_check asc
	     limit $urlCheckQuota",
	    'checkURLs','select');
while($row=mysql_fetch_assoc($result))
     {
     $url=addslashes(strtr($row['url'],
           array_flip(get_html_translation_table(HTML_ENTITIES,ENT_QUOTES))));
     $rc=getCommand("$wgetPath -q --spider '$url' >/dev/null;echo $?");
     sql('update messages
	  set url_check=now()'.($rc==0 ? ",url_check_success='$now'" : '').
	' where id='.$row['id'],
	 'checkURLs','store');
     }
}

dbOpen();
session(getShamesId());
initCheckTimes();
checkURLs();
dbClose();
?>
