<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/errors.php');
require_once('lib/bug.php');
require_once('lib/utils.php');
require_once('lib/post.php');
require_once('lib/logs.php');
require_once('lib/sessions.php');
require_once('lib/users.php');

function startSession()
{
global $login,$password,$siteDomain,$sid;

$id=getUserIdByLoginPassword(addslashes($login),$password);
if($id==0)
  return EL_INVALID;
logEvent('login',"user($id)");
$sid=createSession($id,$id);
SetCookie('sessionid',$sid,time()+7200,'/',$siteDomain);
return EL_OK;
}

postString('login');
postString('password');

dbOpen();
$err=startSession();
if($err==EL_OK)
  header('Location: /actions/checkcookies.php?'.
          makeQuery(array('svalue'  => $sid,
	                  'okdir'   => $okdir,
			  'faildir' => $faildir)));
else
  header('Location: '.remakeURI($faildir,
                                array(),
		  	        array('err' => $err)).'#error');
dbClose();
?>
