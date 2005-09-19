<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/uri.php');
require_once('lib/post.php');
require_once('lib/random.php');
require_once('lib/errors.php');
require_once('lib/session.php');
require_once('lib/logging.php');

postString('okdir');
postString('faildir');

dbOpen();
session();
$err=logout($sessionid);
if($err==ELO_OK)
  header('Location: '.remakeURI($okdir,
				array(),
				array('reload' => random(0,999))));
else
  header('Location: '.remakeURI($faildir,
				array(),
				array('err' => $err)));
dbClose();
?>
