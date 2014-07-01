<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/old-ids.php');

dbOpen();
session();
header('Content-Type: text/plain');
$iter = new OldIdsIterator();
foreach ($iter as $item)
    echo $item->getTableName().' '.$item->getOldId()
                              .' '.$item->getEntryId()."\n";
dbClose();
?>
