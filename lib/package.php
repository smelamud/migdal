<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/logs.php');
require_once('lib/packages.php');

dbOpen();
$id=addslashes($id);
$package=getPackageContentById($id);
if($package->getURL()!='')
  reload($package->getURL());
logEvent('download','packid('.$package->getId().')');
header('Content-Type: '.$package->getMimeType());
echo $package->getBody();
dbClose();
?>
