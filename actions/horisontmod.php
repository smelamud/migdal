<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/horisonts.php');
require_once('lib/errors.php');

function updateHorisont($host,$we_know,$they_know)
{
global $userAdminUsers;

if(!$userAdminUsers)
  return EH_NO_MODIFY;
setHorisont($host,$we_know,HOR_WE_KNOW);
setHorisont($host,$they_know,HOR_THEY_KNOW);
return EH_OK;
}

postString('host');
postInteger('we_know');
postInteger('they_know');

dbOpen();
session();
$err=updateHorisont($host,$we_know,$they_know);
if($err==EH_OK)
  header('Location: '.remakeURI($okdir,
                                array('err')));
else
  header('Location: '.remakeMakeURI($faildir,
                                    $HTTP_POST_VARS,
				    array('okdir',
				          'faildir'),
				    array('err' => $err)));
dbClose();
?>
