<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/errors.php');

postInteger('svalue');

dbOpen();
if($sessionid==$svalue)
  header("Location: $okdir");
else
  header('Location: '.remakeURI($faildir,
                                array(),
		  	        array('err' => EL_NO_COOKIES)).'#error');
dbClose();
?>
