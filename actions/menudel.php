<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/errors.php');
require_once('lib/menu.php');

function menuDelete($id)
{
global $userAdminMenu;

if(!$userAdminMenu)
  return EMID_NO_EDIT;
if(!menuItemExists($id))
  return EMID_NO_ITEM;
if(!mysql_query("delete from menu where id=$id"))
  return EMID_SQL;
return EMID_OK;
}

settype($id,'integer');

dbOpen();
session($sessionid);
mysql_query('lock tables menu write')
     or die('Ошибка при блокировании таблицы menu');
$err=menuDelete($id);
mysql_query('unlock tables')
     or die('Ошибка при разблокировании таблиц');
if($err==EMID_OK)
  header('Location: '.remakeURI($redir,array('err'),array('menuid' => 0)));
else
  header('Location: '.remakeURI($redir,array(),array('err' => $err)));
dbClose();
?>
