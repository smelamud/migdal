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
header('Content-Type: text/xml');
$func='get'.camelCase($name).'XML';
echo '<mtext>';
echo iconv('koi8-r','utf-8',$posting->$func());
echo '</mtext>';
dbClose();
?>
