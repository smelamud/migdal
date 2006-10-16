<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/catalog.php');

postString('okdir');
postString('faildir');

postInteger('topic_id');

dbOpen();
session();
$catalog=catalogById($topic_id);
if($catalog!='')
  header("Location: $okdir$catalog");
else
  header("Location: $faildir");
dbClose();
?>