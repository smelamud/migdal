<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/errors.php');
require_once('lib/menu.php');

function setMenuIndex($id,$index)
{
return mysql_query("update menu set menu_index=$index where id=$id");
}

function menuExchange($firstid,$secondid)
{
global $userAdminMenu;

if(!$userAdminMenu)
  return EMIX_NO_EDIT;
$firstIndex=getMenuIndexById($firstid);
if($firstIndex<0)
  return EMIX_NO_FIRST;
$secondIndex=getMenuIndexById($secondid);
if($secondIndex<0)
  return EMIX_NO_SECOND;
if(!setMenuIndex($firstid,$secondIndex))
  return EMIX_SQL_FIRST;
if(!setMenuIndex($secondid,$firstIndex))
  return EMIX_SQL_SECOND;
return EMIX_OK;
}

postInteger('firstid');
postInteger('secondid');

dbOpen();
session($sessionid);
mysql_query('lock tables menu write')
     or die('Ошибка при блокировании таблицы menu');
$err=menuExchange($firstid,$secondid);
mysql_query('unlock tables')
     or die('Ошибка при разблокировании таблиц');
if($err==EMIX_OK)
  {
  srand(time());
  header('Location: '.remakeURI($redir,
                                array('err'),
				array('reload' => rand(0,999))));
  }
else
  header('Location: '.remakeURI($redir,array(),array('err' => $err)));
dbClose();
?>
