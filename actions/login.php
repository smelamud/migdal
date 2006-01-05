<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/errors.php');
require_once('lib/bug.php');
require_once('lib/utils.php');
require_once('lib/post.php');
require_once('lib/session.php');
require_once('lib/logging.php');

postString('okdir');
postString('faildir');
postString('login');
postString('password');

dbOpen();
session();
$err=login($login,$password);
if($err==EG_OK)
  header('Location: /actions/checkcookies?'.
          makeQuery(array('svalue'  => $sessionid,
	                  'okdir'   => $okdir,
			  'faildir' => $faildir)));
else
  header('Location: '.remakeURI($faildir,
                                array(),
		  	        array('err' => $err)).'#error');
dbClose();
?>
