<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/errors.php');
require_once('lib/utils.php');

function modifyMessage($editid,$hide)
{
global $userModerator;

if(!$userModerator)
  return EMH_NO_MODERATE;
$result=mysql_query("select id
                     from messages
		     where id=$editid")
	     or die('Ошибка SQL при выборке сообщения');
if(mysql_num_rows($result)<=0)
  return EMH_NO_MESSAGE;
$result=mysql_query("update messages
                     set disabled=$hide
		     where id=$editid")
	     or die('Ошибка SQL при модерировании сообщения');
return EMH_OK;
}

postInteger('editid');

dbOpen();
session($sessionid);
$err=modifyMessage($editid,$hide ? 1 : 0);
if($err==EMH_OK)
  header('Location: '.remakeURI($redir,array(),array('reload' => rand(0,999))));
else
  header('Location: '.remakeURI($redir,array(),array('err' => $err)).'#error');
dbClose();
?>
