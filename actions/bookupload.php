<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/errors.php');
require_once('lib/packages.php');

postInteger('message_id');
postInteger('type');

function dropBook($message_id,$type)
{
mysql_query("delete from packages
             where message_id=$message_id and type=$type")
  or sqlbug('Ошибка SQL при удалении старого пакета');
}

function uploadBook($message_id,$type,$mime_type,$fname)
{
$package=new Package(array('message_id' => $message_id,
			   'type'       => $type,
			   'mime_type'  => $mime_type,
			   'size'       => filesize("$bookCompressDir/$fname"),
			   'url'        => "$bookCompressURL/$fname"));
if(!$package->store())
  sqlbug('Ошибка SQL при добавлении пакета');
}

dbOpen();
session(getShamesId());
dropBook($message_id,$type);
uploadBook($message_id,$type,$mime_type,$fname);
dbClose();
?>
