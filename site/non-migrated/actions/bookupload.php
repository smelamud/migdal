<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/errors.php');
require_once('lib/packages.php');
require_once('lib/sql.php');

httpRequestInteger('message_id');
httpRequestInteger('type');

function dropBook($message_id,$type)
{
sql("delete from packages
     where message_id=$message_id and type=$type",
    'dropBook');
}

function uploadBook($message_id,$type,$mime_type,$fname)
{
global $bookCompressDir,$bookCompressURL;

$package=new Package(array('message_id' => $message_id,
			   'type'       => $type,
			   'mime_type'  => $mime_type,
			   'size'       => filesize("$bookCompressDir/$fname"),
			   'url'        => "$bookCompressURL/$fname"));
# FIXME метод store() должен быть заменен на функцию storeObject(&$obj)
$package->store();
}

dbOpen();
session(getShamesId());
dropBook($message_id,$type);
uploadBook($message_id,$type,$mime_type,$fname);
dbClose();
?>
