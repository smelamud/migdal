<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/complainactions.php');
require_once('lib/errors.php');

function deleteComplainAction($id)
{
return mysql_query("delete from complain_actions
                    where id=$id");
}

function removeComplainAction($editid)
{
global $userAdminComplainAnswers;

if(!$userAdminComplainAnswers)
  return ECAD_NO_EDIT;
if(!complainActionExists($editid))
  return ECAD_NO_ACTION;
if(!deleteComplainAction($editid))
  return ECAD_DELETE_SQL;
return ECAD_OK;
}

settype($editid,'integer');

dbOpen();
session($sessionid);
$err=removeComplainAction($editid);
if($err==ECAD_OK)
  header('Location: '.remakeURI($redir,array('err'),array('editid' => 0)));
else
  header('Location: '.remakeURI($redir,array(),array('err' => $err)));
dbClose();
?>
