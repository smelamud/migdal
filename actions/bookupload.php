<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/errors.php');

postInteger('message_id');
postInteger('type');
postInteger('mime_type');
postInteger('fname');

dbOpen();
session(getShamesId());
$package=new Package(array('message_id' => $message_id,
			   'type'       => $type,
			   'mime_type'  => $bookCompressType,
			   'size'       => filesize("$bookCompressDir/$fname"),
			   'url'        => "$bookCompressURL/$fname"));
}
if(!$package->store())
  echo "PDF book ($message_id) registration error: ".mysql_error()."\n";
dbClose();
?>
