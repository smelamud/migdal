<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/inner-images.php');
require_once('lib/image-files.php');
require_once('lib/answers.php');
require_once('lib/logs.php');

dbOpen();
session(getShamesId());

purgeLogs();
answersRecalculate();
deleteObsoleteInnerImages();
deleteObsoleteImageFiles();

dbClose();
?>
