<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/users.php');

function initCheckTimes()
{
mysql_query('update messages
             set url_check=sent,url_check_success=sent
	     where url_check=0 or url_check_success=0')
     or die('Ошибка SQL при инициализации времени проверки URL');
journal('update messages
         set url_check=sent,url_check_success=sent
	 where url_check=0 or url_check_success=0');
}

function checkURLs()
{
global $urlCheckTimeout,$urlCheckQuota,$wgetPath;

$result=mysql_query("select id,url
                     from messages
		     where url like '%://%' and
		           url_check+interval $urlCheckTimeout day<now()
		     order by url_check asc
		     limit $urlCheckQuota");
if(!$result)
  die('Ошибка SQL при выборке URL для проверки');
while($row=mysql_fetch_assoc($result))
     {
     $output=array();
     $url=addslashes(strtr($row['url'],
           array_flip(get_html_translation_table(HTML_ENTITIES,ENT_QUOTES))));
     exec("$wgetPath -q --spider '$url'",$output,$rc);
     mysql_query('update messages
                  set url_check=now()'.($rc==0 ? ',url_check_success=now()' : '').
		' where id='.$row['id'])
	  or die('Ошибка SQL при сохранении результатов проверки URL');
     journal('update messages
              set url_check=now()'.($rc==0 ? ',url_check_success=now()' : '').
	    ' where id='.journalVar('messages',$row['id']));
     }
}

dbOpen();
session(getShamesId());
initCheckTimes();
checkURLs();
dbClose();
?>
