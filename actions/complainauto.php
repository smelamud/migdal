<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/errors.php');
require_once('lib/users.php');
require_once('lib/complains.php');

function setAutoComplain($id,$no_auto)
{
global $userId;

$complain=getComplainInfoById($id);
if($complain->getId()==0)
  return EC_NO_COMPLAIN;
if($complain->getRecipientId()!=$userId)
  return EC_NO_AUTO;
$result=mysql_query("update complains
                     set no_auto=$no_auto
		     where id=$id");
if(!$result)
  return EC_SQL_AUTO;
return EC_OK;
}

postInteger('id');
postInteger('no_auto');

dbOpen();
session();
$err=setAutoComplain($id,$no_auto);
header('Location: '.($err==EC_OK ? remakeURI($okdir,array('err'))
                                 : remakeURI($faildir,
				             array(),
					     array('err' => $err))));
dbClose();
?>
