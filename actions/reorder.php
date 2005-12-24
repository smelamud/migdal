<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/errors.php');
require_once('lib/postings.php');
require_once('lib/sql.php');

function reorderArts($art)
{
$uniq=array_unique($art);
if(count($uniq)!=count($art))
  return EO_DUPS;
$index=0;
foreach($art as $id)
       {
       $posting=getPostingById($id);
       if($posting->getId()<=0)
         return EO_NO_ARTICLE;
       if(!$posting->isWritable())
         return EO_NO_REORDER;
       sql("update postings
	    set index0=$index
	    where id=$id",
	   'reorderArts');
       journal("update postings
                set index0=$index
		where id=".journalVar('postings',$id));
       $index++;
       }
return EG_OK;
}

postIntegerArray('art');

dbOpen();
session();
$err=reorderArts($art);
if($err==EG_OK)
  header("Location: $okdir");
else
  header('Location: '.remakeMakeURI($faildir,
                                    $HTTP_POST_VARS,
				    array('okdir',
				          'faildir'),
				    array('err' => $err)).'#error');
dbClose();
?>
