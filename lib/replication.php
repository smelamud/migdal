<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/no-cache.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/journal.php');
require_once('lib/redirs.php');

settype($from,'integer');

dbOpen();
session();
redirect();

noCacheHeaders();
header("Content-Type: $replicationType");
$iter=new JournalIterator($from);
while($line=$iter->next())
     echo $line->getId()."\t".$line->getSeq().
          "\t".$line->getResultTableTransfer()."\t".$line->getResultId().
	  "\t".$line->getResultVar()."\t".$line->getQueryTransfer()."\n";

dbClose();
?>
