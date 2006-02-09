<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/postings.php');
require_once('lib/text.php');

postIdent('id');
postString('name');

dbOpen();
session();
$posting=getPostingById($id,GRP_ALL,-1,-1,SELECT_GENERAL|SELECT_LARGE_BODY);
$func='get'.camelCase($name).'XML';
$data=iconv('koi8-r','utf-8',$posting->$func());
$size=strlen($data);
header('Content-Type: text/xml');
header("Content-Disposition: inline; filename=migdal-$id-$name.xml; size=$size");
echo '<mtext>';
echo $data;
echo '</mtext>';
dbClose();
?>
