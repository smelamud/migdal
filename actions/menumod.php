<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/errors.php');
require_once('lib/menu.php');
require_once('lib/ident.php');

function modifyMenu($item)
{
global $userAdminMenu;

if(!$userAdminMenu)
  return EMI_NO_EDIT;
if($item->name=='')
  return EMI_NAME_ABSENT;
if($item->ident=='')
  return EMI_IDENT_ABSENT;
$id=idByIdent('menu',$item->ident);
if($item->id!=$id && $id!=0)
  return EMI_IDENT_UNIQUE;
if(!$item->store())
  return EMI_STORE_SQL;
return EMI_OK;
}

settype($editid,'integer');

dbOpen();
session($sessionid);
$item=getMenuItemById($editid);
$item->setup($HTTP_POST_VARS);
$err=modifyMenu($item);
if($err==EMI_OK)
  header("Location: /menuedit.php?menuid=$editid");
else
  header('Location: /menuedit.php?'.makeQuery($HTTP_POST_VARS,
                                              array('editid'),
				              array('menuid' => $editid,
                                                    'err'    => $err)));
dbClose();
?>
