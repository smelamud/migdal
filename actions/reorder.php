<?php
# @(#) $Id$

require_once('lib/errorreporting.php');
require_once('lib/database.php');
require_once('lib/session.php');
require_once('lib/post.php');
require_once('lib/errors.php');
require_once('lib/postings.php');

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
       $result=mysql_query("update postings
                            set index0=$index
			    where id=$id");
       if(!$result)
         return EO_SQL;
       journal("update postings
                set index0=$index
		where id=".journalVar('postings',$id));
       $index++;
       }
return EO_OK;
}

postIntegerArray('art');

dbOpen();
session();
$err=reorderArts($art);
if($err==EO_OK)
  header("Location: $okdir");
else
  header('Location: '.remakeMakeURI($faildir,
                                    $HTTP_POST_VARS,
				    array('okdir',
				          'faildir'),
				    array('err' => $err)).'#error');
dbClose();
?>
