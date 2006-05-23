<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/errors.php');
require_once('lib/entries.php');
require_once('lib/sql.php');
require_once('lib/permissions.php');

function positive($n)
{
return $n>0;
}

function reorderAction($ids,$count)
{
$ids=array_filter($ids,'positive');
if(count($ids)<$count)
  return EO_LITTLE;
$uniq=array_unique($ids);
if(count($uniq)!=count($ids))
  return EO_DUPS;
foreach($art as $id)
       {
       $perms=getPermsById($id);
       if(!$perms)
         return EO_NO_ENTRY;
       if(!$perms->isWritable())
         return EO_NO_REORDER;
       }
reorderEntries($ids);
return EG_OK;
}

postString('okdir');
postString('faildir');

postIntegerArray('ids');
postInteger('count');

dbOpen();
session();
$err=reorderAction($ids,$count);
if($err==EG_OK)
  header("Location: $okdir");
else
  header('Location: '.remakeURI($faildir,
				array('okdir',
				      'faildir'),
				array('edittag' => 1,
				      'ids' => $ids,
				      'err' => $err)).'#error');
dbClose();
?>
