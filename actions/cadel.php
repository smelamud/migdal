<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/complainactions.php');
require_once('lib/errors.php');
require_once('lib/sql.php');

function removeComplainAction($editid)
{
global $userAdminComplainAnswers;

if(!$userAdminComplainAnswers)
  return ECAD_NO_EDIT;
if(!complainActionExists($editid))
  return ECAD_NO_ACTION;
deleteComplainAction($editid);
return EG_OK;
}

postString('okdir');
postString('faildir');

postInteger('editid');

dbOpen();
session();
$err=removeComplainAction($editid);
if($err==EG_OK)
  header('Location: '.remakeURI($okdir,
                                array('err'),
				array('editid' => 0)));
else
  header('Location: '.remakeURI($faildir,
                                array(),
				array('err' => $err)));
dbClose();
?>
