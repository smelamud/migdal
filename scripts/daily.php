<?php
# @(#) $Id$

require_once('conf/migdal.conf');

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/users.php');

function tag($table)
{
mysql_query("update $table
             set used=1")
     or die(mysql_error());
}

function useLinks($sourceTable,$sourceField,$destTable,$destField)
{
$result=mysql_query("select $destField
                     from $destTable
		     where $destField<>0");
if(!$result)
  die(mysql_error());
while($row=mysql_fetch_array($result))
     mysql_query("update $sourceTable
                  set used=0
		  where $sourceField=".$row[0])
          or die(mysql_error());
}

function deleteTagged($table)
{
mysql_query("delete
             from $table
	     where used=1")
     or die(mysql_error());
mysql_query("optimize table $table")
     or die(mysql_error());
}

function cleanup()
{
tag('images');
useLinks('images','image_set','stotexts','image_set');
useLinks('images','image_set','stotexts','large_imageset');
deleteTagged('images');
}

dbOpen();
session(getShamesId());
cleanup();
dbClose();

?>
