<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/logs.php');
require_once('lib/images.php');
require_once('lib/utils.php');
require_once('lib/counters.php');

$message_id=addslashes($message_id);
$image_id=addslashes($image_id);

dbOpen();
incCounter($message_id,CMODE_EAR_HITS);
$image=getImageContentById($image_id,'large');
header('Content-Type: ',$image->getFormat());
echo $image->getLarge();
dbClose();
?>
