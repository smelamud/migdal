<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/postings.php');
require_once('lib/postings-info.php');
require_once('lib/bug.php');

function deletePosting($id)
{
if(!postingExists($id))
  return EPD_POSTING_ABSENT;
$msgid=getMessageIdByPostingId($id);
$perms=getPermsById('messages',$msgid);
if(!$perms->isWritable())
  return EPD_NO_DELETE;
$result=mysql_query("select id
                     from postings
		     where message_id=$msgid and id<>$id
		     order by shadow asc
		     limit 1")
	  or sqlbug('Ошибка SQL при выборке остальных теней');
if(mysql_num_rows($result)!=0)
  {
  $rid=mysql_result($result,0,0);
  mysql_query("update postings
               set shadow=0
	       where id=$rid")
    or sqlbug('Ошибка SQL при установке флага тени');
  journal('update postings
           set shadow=0
	   where id='.journalVar('postings',$rid));

  }
mysql_query("delete from postings
             where id=$id")
  or sqlbug('Ошибка SQL при удалении постинга');
journal('delete from postings
         where id='.journalVar('postings',$id));
return EPD_OK;
}

postInteger('id');

dbOpen();
session();
$err=deletePosting($id);
if($err==EPD_OK)
  {
  header('Location: '.remakeURI($okdir,array('err')));
  dropPostingsInfoCache(DPIC_POSTINGS);
  }
else
  header('Location: '.remakeURI($faildir,array(),array('err' => $err)));
dbClose();
?>
